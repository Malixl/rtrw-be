📁 Postman files for RTRW API

How to use

1. Import `RTRW-api.postman_collection.json` into Postman.
2. Set environment variable `base_url` (e.g. http://localhost:8000/api).
3. If endpoints require auth, set `token` to a valid Bearer token (Sanctum or your auth mechanism).
4. Example import files are under `postman/imports`:
    - `layer_groups_import.json` — single LayerGroup object using Rafiq's requested grouped klasifikasi shape (ready to POST as raw JSON)
    - `data_spasial_sample.geojson` — sample geojson for file upload tests
    - `sample_polygons.geojson` — sample polygon GeoJSON for Polaruang, StrukturRuang, KetentuanKhusus tests
    - `sample_doc.txt` — sample document for IndikasiProgram upload tests

Quick notes

-   Use the "Create Pola Ruang (file upload)" and "Create Data Spasial (file upload)" requests to test file upload (attach the sample geojson file).
-   For importing LayerGroups in bulk, you can write a small script that POSTs each object in `layer_groups_import.json` to `POST /api/layer-groups` or use the server bulk import endpoint `POST /api/layer-groups/import` which accepts an array in Rafiq's format (recommended).

Bulk import (server)

-   Endpoint: `POST {{base_url}}/layer-groups/import` (Admin only - include Authorization header `Bearer {{token}}`)
-   Body: raw JSON array (see `postman/imports/layer_groups_import.json` for single item example)
-   Success: HTTP 201 with `{ code:201, status:true, message: 'Layer groups imported successfully', data: [ ... ] }` where `data` returns the created LayerGroup resources in Rafiq's format.
-   Validation errors: HTTP 422 with `{ code:422, status:false, message: 'Invalid payload', errors: { ... } }`

Client-side import (if you prefer not to use server import)

-   Use Postman Runner or the example Node snippet below (run locally, do not commit) to loop through `postman/imports/layer_groups_bulk_import.json` and create LayerGroup + klasifikasi per item.

```js
// local helper (not committed)
const axios = require("axios");
const data = require("./postman/imports/layer_groups_bulk_import.json");
(async () => {
    for (const group of data) {
        const resp = await axios.post(
            "{{base_url}}/layer-groups",
            {
                layer_group_name: group.layer_group_name,
                deskripsi: group.deskripsi,
                urutan_tampil: group.urutan_tampil,
            },
            { headers: { Authorization: `Bearer ${process.env.TOKEN}` } }
        );

        const lg = resp.data.data;

        // create klasifikasi per tipe
        const mapping = {
            klasifikasi_pola_ruang: "pola_ruang",
            klasifikasi_struktur_ruang: "struktur_ruang",
            klasifikasi_ketentuan_khusus: "ketentuan_khusus",
            klasifikasi_pkkprl: "pkkprl",
            klasifikasi_indikasi_program: "indikasi_program",
        };

        for (const key in mapping) {
            const tipe = mapping[key];
            const items = group.klasifikasis?.[key] || [];
            for (const it of items) {
                await axios.post(
                    "{{base_url}}/klasifikasi",
                    {
                        nama: it.nama,
                        deskripsi: it.deskripsi || "",
                        tipe,
                        layer_group_id: lg.id,
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${process.env.TOKEN}`,
                        },
                    }
                );
            }
        }
    }
    console.log("Import finished");
})();
```

If you want, I can also:

-   Add a simple import route/controller that accepts the full `layer_groups_import.json` and creates LayerGroups + Klasifikasis in one request.
-   Clean duplicates in `database/seeders/UserSeeder.php` and remove unused `manajemen_wilayah` permission if you confirm it's obsolete.
