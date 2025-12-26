# Layer Groups - Postman

This Postman collection contains detailed CRUD requests and Map-specific endpoints for LayerGroups and related entities (Klasifikasi, Pola Ruang, Struktur Ruang, etc.). Use it to test and validate both the default grouped response and the flat per-type response requested by the frontend.

---

## Endpoint overview

- Default grouped map endpoint:
  - GET /api/layer-groups/with-klasifikasi
  - Query params: `rtrw_id` (optional), `only_with_children` (optional, default: `true`), `format=group` (default)
  - Response: list of LayerGroups ordered by `urutan_tampil`, each containing `klasifikasis` and nested geo children arrays (`pola_ruang`, `struktur_ruang`, `ketentuan_khusus`, `indikasi_program`, `pkkprl`, `data_spasial`).

- Flat per-type endpoint (Rafiq's example):
  - GET /api/layer-groups/with-klasifikasi?format=flat&rtrw_id=<id>
  - Requirements: `rtrw_id` is required for `format=flat`.
  - Response: object with `rtrw` meta and per-type arrays:
    - `klasifikasi_pola_ruang`, `klasifikasi_struktur_ruang`, `klasifikasi_ketentuan_khusus`, `klasifikasi_indikasi_program`, `klasifikasi_pkkprl`, `klasifikasi_data_spasial`.

---

## Quick test flow (recommended order)

1) Login (POST `/auth/login`) — set `{{token}}` environment variable if returned in the response.
2) Create Periode and RTRW (via API or DB scripts) — set `{{rtrw_id}}`.
3) Create LayerGroup (POST `/layer-groups` with Authorization) — set `{{layer_group_id}}` from response.
4) Create Klasifikasi (POST `/klasifikasi` with `layer_group_id` and `rtrw_id`) — set `{{klasifikasi_id}}`.
5) Create child geo entities (Pola Ruang, Struktur Ruang, etc.) for the klasifikasi.
6) Test Map endpoints:
   - Group format: GET `/layer-groups/with-klasifikasi?rtrw_id={{rtrw_id}}&only_with_children=true`
   - Flat format: GET `/layer-groups/with-klasifikasi?format=flat&rtrw_id={{rtrw_id}}`

---

## Test expectations / example responses

Example (group format):

{
  "code": 200,
  "status": true,
  "message": "Data layer group dengan klasifikasi berhasil diambil",
  "data": [ /* LayerGroupMapResource array */ ],
  "pagination": null
}

Example (flat format):

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

- The `Auth - Login` request includes a test script that sets `{{token}}` if response JSON includes `data.token`.
- Create requests attempt to set created resource ids in environment variables (e.g., `{{layer_group_id}}`, `{{klasifikasi_id}}`).

---

If you want I can also export and share a ready-to-run Postman environment file with sample values for `{{base_url}}`, `{{admin_email}}`, `{{admin_password}}`, `{{rtrw_id}}` so Rafiq can run the collection end-to-end.