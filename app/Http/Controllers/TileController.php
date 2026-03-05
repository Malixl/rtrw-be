<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TileController extends Controller
{
    /**
     * Serve individual .pbf tile from .mbtiles (SQLite3) database.
     *
     * MBTiles is a standard SQLite format with a `tiles` table:
     *   - zoom_level (int)
     *   - tile_column (int) = x
     *   - tile_row (int) = TMS y (flipped from XYZ)
     *   - tile_data (blob) = gzipped protobuf
     *
     * @param  string  $layer  Nama file .mbtiles (tanpa ekstensi)
     * @param  int  $z  Zoom level
     * @param  int  $x  Tile column
     * @param  int  $y  Tile row (XYZ scheme — akan di-flip ke TMS)
     */
    public function serve($layer, $z, $x, $y)
    {
        // Sanitize input
        $z = (int) $z;
        $x = (int) $x;
        $y = (int) $y;
        $layer = preg_replace('/[^a-zA-Z0-9_\-]/', '', $layer);

        $mbtilesPath = Storage::disk('public')->path("tiles/{$layer}.mbtiles");

        if (!file_exists($mbtilesPath)) {
            return response('', 404);
        }

        try {
            $db = new \SQLite3($mbtilesPath, SQLITE3_OPEN_READONLY);

            // MBTiles uses TMS y-coordinate (flipped from XYZ)
            $tmsY = (1 << $z) - 1 - $y;

            $stmt = $db->prepare(
                'SELECT tile_data FROM tiles WHERE zoom_level = :z AND tile_column = :x AND tile_row = :y'
            );
            $stmt->bindValue(':z', $z, SQLITE3_INTEGER);
            $stmt->bindValue(':x', $x, SQLITE3_INTEGER);
            $stmt->bindValue(':y', $tmsY, SQLITE3_INTEGER);

            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            $db->close();

            if (!$row || !$row['tile_data']) {
                // Tile kosong untuk area ini — normal, bukan error
                return response('', 204);
            }

            return response($row['tile_data'], 200, [
                'Content-Type' => 'application/x-protobuf',
                'Content-Encoding' => 'gzip',
                'Access-Control-Allow-Origin' => '*',
                'Cache-Control' => 'public, max-age=86400', // Cache 1 hari
                'ngrok-skip-browser-warning' => 'true',
            ]);

        } catch (\Exception $e) {
            Log::error("TileController: Error serving tile {$layer}/{$z}/{$x}/{$y}", [
                'error' => $e->getMessage(),
            ]);

            return response('', 500);
        }
    }
}
