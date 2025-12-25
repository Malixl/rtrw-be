# ERD (Entity Relationship Diagram) - RTRW Project

Files:

-   `database_erd.puml` — original PlantUML source for the ERD
-   `database_full_erd.puml` — full PlantUML ERD (user/roles, periode, RTRW, LayerGroup, klasifikasi, children, berita, batas administrasi) based on the provided diagram

How to generate an image (PNG/SVG):

Option A — Using PlantUML CLI (requires Java and plantuml.jar):

1. Install PlantUML: https://plantuml.com/starting
2. Run:
    ```bash
    java -jar plantuml.jar docs/erd/database_erd.puml
    ```
    This will create `database_erd.png` alongside the `.puml` file.

Option B — VSCode PlantUML extension:

1. Install "PlantUML" extension by jebbs
2. Open `database_erd.puml`
3. Use preview and export PNG/SVG

Notes:

-   Diagram shows core entities and relations added for the Layer Group feature:
    Periode → Rtrw → LayerGroup → Klasifikasi → (PolaRuang, StrukturRuang, KetentuanKhusus, PKKPRL, DataSpasial, IndikasiProgram)
-   `klasifikasi.layer_group_id` is nullable and uses `onDelete('set null')` as designed.

If you want an auto-generated PNG checked in too, I can generate it and commit (requires plantuml available locally).
