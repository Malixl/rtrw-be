# Layer Groups - Postman

Endpoint: `GET /api/layer-groups/with-klasifikasi`

Notes:

-   Use environment variable `{{baseUrl}}` and set it to your server root (example: `http://127.0.0.1:8000`). Do not include a trailing `/` or `/api` part — the collection prepends `/api` when sending auth requests.
-   Use `{{admin_email}}` and `{{admin_password}}` (test admin) for auto-login; the prerequest script will login and set `{{token}}` if those are set in the environment.

Default grouped map endpoint:

-   GET /api/layer-groups/with-klasifikasi
-   Query params: `rtrw_id` (optional), `only_with_children` (optional, default: `true`), `format=group` (default)
-   Response: list of LayerGroups ordered by `urutan_tampil`, each containing a `klasifikasis` **object keyed per-type** (e.g. `klasifikasi_pola_ruang`) — each klasifikasi object includes its child geo arrays (`pola_ruang`, `struktur_ruang`, `ketentuan_khusus`, `indikasi_program`, `pkkprl`, `data_spasial`) and timestamps in ISO when `raw_dates=true` (child geo items now include `klasifikasi_id` rather than a nested `klasifikasi` object).

Flat per-type endpoint (Rafiq's example):

-   GET /api/layer-groups/with-klasifikasi?format=flat&rtrw_id=<id>
-   Requirements: `rtrw_id` is required for `format=flat`.
-   Response: object with `rtrw` meta and per-type arrays:
    -   `klasifikasi_pola_ruang`, `klasifikasi_struktur_ruang`, `klasifikasi_ketentuan_khusus`, `klasifikasi_indikasi_program`, `klasifikasi_pkkprl`, `klasifikasi_data_spasial`.

---

## Quick test flow (recommended order)

1. Login (POST `/api/auth/login`) — set `{{token}}` environment variable if returned in the response (collection will auto-login if `{{admin_email}}` and `{{admin_password}}` exist and no `{{token}}` is set).
2. Create Periode and RTRW (via API or DB scripts) — set `{{rtrw_id}}`.
3. Create LayerGroup (POST `/api/layer-groups` with Authorization) — set `{{layer_group_id}}` from response.
4. Create Klasifikasi (POST `/api/klasifikasi` with `layer_group_id` and `rtrw_id`) — set `{{klasifikasi_id}}`.
5. Create child geo entities (Pola Ruang, Struktur Ruang, etc.) for the klasifikasi.
6. Test Map endpoints:
    - Group format: GET `/api/layer-groups/with-klasifikasi?rtrw_id={{rtrw_id}}&only_with_children=true`
    - Flat format: GET `/api/layer-groups/with-klasifikasi?format=flat&rtrw_id={{rtrw_id}}`

-   Compact behavior: by default map endpoints **include empty child arrays** (for example `pola_ruang: []`) so front-end can safely `map()` over them. To hide empty relations instead, add `&compact=true` to the query string (example: `/api/layer-groups/with-klasifikasi?rtrw_id={{rtrw_id}}&only_with_children=true&compact=true`).
-   Date format: map endpoints return ISO 8601 timestamps by default (`raw_dates=true`). To get human-readable dates, add `&raw_dates=false`.

## Cara melihat detail Layer Group beserta geometrinya (Postman) ✅

1. Pastikan environment:

    - `{{base_url}}` = `http://localhost:8000` (atau URL server Anda).
    - `{{rtrw_id}}` telah terisi (contoh: `1`).
    - `{{layer_group_id}}` telah terisi (id layer group yang ingin dilihat).

2. Jalankan request **"LayerGroups - Show (with klasifikasi & geojson)"** di koleksi ini.

    - Request ini memanggil endpoint: `GET /api/layer-groups/with-klasifikasi?rtrw_id={{rtrw_id}}&only_with_children=true`.

3. Yang perlu diperiksa di response:

    - Response code harus `200`.
    - Cari object dengan `id` sama seperti `{{layer_group_id}}` di `response.data`.
    - Pastikan object tersebut memiliki `klasifikasis` dan setiap klasifikasi memiliki salah satu properti geochild: `pola_ruang`, `struktur_ruang`, `ketentuan_khusus`, `indikasi_program`, `pkkprl`, atau `data_spasial`.
    - Contoh path yang bisa diperiksa di Postman: `response.data[?].klasifikasis[*].pola_ruang[*].geojson`

4. Skrip Test otomatis (terpasang pada request):

    - Request akan memverifikasi bahwa ada layer group dengan `{{layer_group_id}}` dan setidaknya satu klasifikasi memiliki geochildren.

5. Jika test gagal: periksa apakah data geo (Pola Ruang / Struktur Ruang / dsb.) sudah dibuat dan terkait dengan klasifikasi yang benar.

Tip cepat: setelah test lulus, request menyimpan object layer group yang ditemukan di environment variable `{{last_layer_group}}` untuk pemeriksaan lanjutan.

---

## Test expectations / example responses

Example response (group format):
{
"code": 200,
"status": true,
"message": "Data layer group dengan klasifikasi berhasil diambil",
"data": [ /* LayerGroupMapResource array */ ],
"pagination": null
}

Example response (flat format):
{
"code": 200,
"status": true,
"message": "Data klasifikasi per type berhasil diambil",
"data": {
"rtrw": { "id": 1, "nama": "RTRW X" },
"klasifikasi_pola_ruang": [...],
"klasifikasi_struktur_ruang": [...],
"klasifikasi_ketentuan_khusus": [...],
"klasifikasi_indikasi_program": [...],
"klasifikasi_pkkprl": [...],
"klasifikasi_data_spasial": [...]
}
}

---

## Postman scripting notes

-   The `Auth - Login` request includes a test script that sets `{{token}}` if response JSON includes `data.token` or `access_token`.
-   The collection has a prerequest script that will attempt an auto-login to `/api/auth/login` when `{{admin_email}}` and `{{admin_password}}` are provided and `{{token}}` is not set.
-   Create requests attempt to set created resource ids in environment variables (e.g., `{{layer_group_id}}`, `{{klasifikasi_id}}`).

---

If you want I can also export and share a ready-to-run Postman environment file with sample values for `{{baseUrl}}`, `{{admin_email}}`, `{{admin_password}}`, `{{rtrw_id}}` so Rafiq can run the collection end-to-end.
