# Layer Groups - Postman

Endpoint: `GET /api/layer-groups/with-klasifikasi`

Query parameters:
- `rtrw_id` (optional) - integer to filter klasifikasi by RTRW
- `only_with_children` (optional) - boolean (default: `true`) filter out klasifikasi with no geo children

Example request (Postman):
GET {{base_url}}/layer-groups/with-klasifikasi?rtrw_id={{rtrw_id}}&only_with_children=true

Example response (200):
{
  "code": 200,
  "status": true,
  "message": "Data layer group dengan klasifikasi berhasil diambil",
  "data": [ /* LayerGroupMapResource array with nested klasifikasis and geo children */ ],
  "pagination": null
}

Notes:
- This endpoint is public (read-only) and intended for the map landing page to load grouped layers with their nested geo data in a single call.
- If you prefer groups to remain even when they have no klasifikasi (empty arrays), set `only_with_children=false`.
