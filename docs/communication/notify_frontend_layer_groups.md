Hi Rafiq,

Saya sudah menambahkan endpoint untuk kebutuhan map landing:

-   Endpoint: GET /api/layer-groups/with-klasifikasi
-   Query params:
    -   rtrw_id (optional)
    -   only_with_children (optional, default true)

Default behavior:

-   returns LayerGroups ordered by `urutan_tampil` with nested `klasifikasis` (each with nested geo children: `pola_ruang`, `struktur_ruang`, `ketentuan_khusus`, `indikasi_program`, `pkkprl`, `data_spasial`).
-   If `only_with_children=true` (default), groups/klasifikasi without geo children are filtered out.
-   If `only_with_children=false`, klasifikasi will be included even if their geo arrays are empty.

You can test using Postman collection: docs/postman/layer-groups.postman_collection.json (item: "LayerGroups - With Klasifikasi (Map)")

If payload shape needs changes (field names, include extra metadata, change ordering), reply with quick notes and I will adjust.

Contoh response singkat:
{
"code": 200,
"status": true,
"message": "Data layer group dengan klasifikasi berhasil diambil",
"data": [ ... ]
}

Terima kasih, beri tahu saya jika mau perubahan cepat.
