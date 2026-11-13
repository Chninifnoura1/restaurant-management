# Modèle logique de données (MLD)

Les identifiants sont des `BIGINT` auto-incrémentés. Les dates sont en `DATETIME_IMMUTABLE`. Les montants sont en `DECIMAL`, les quantités en `DECIMAL(14,3)` (ou précision supérieure justifiée). Les noms de colonnes sont en `snake_case`.

| Table | Clés et colonnes essentielles |
|---|---|
| user | PK id ; email UNIQUE ; password_hash ; display_name ; is_active |
| role | PK id ; code UNIQUE ; label |
| user_role | PK user_id FK user ; PK role_id FK role |
| category | PK id ; name UNIQUE ; description |
| dish | PK id ; category_id FK ; name ; sale_price DECIMAL(10,2) ; recipe_id UNIQUE FK ; is_active ; archived_at |
| ingredient | PK id ; name ; base_unit ; current_quantity DECIMAL(14,3) ; minimum_quantity DECIMAL(14,3) |
| recipe | PK id ; dish_id UNIQUE FK ; yield_quantity ; instructions |
| recipe_ingredient | PK id ; recipe_id FK ; ingredient_id FK ; quantity ; unit ; UNIQUE(recipe_id, ingredient_id) |
| preparation | PK id ; name ; yield_quantity ; yield_unit ; instructions |
| preparation_ingredient | PK id ; preparation_id FK ; ingredient_id FK ; quantity ; unit |
| recipe_preparation | PK id ; recipe_id FK ; preparation_id FK ; quantity ; unit |
| supplier | PK id ; name ; contact ; email ; phone |
| supplier_ingredient | PK id ; supplier_id FK ; ingredient_id FK ; supplier_reference ; last_purchase_price ; UNIQUE(supplier_id, ingredient_id) |
| purchase | PK id ; supplier_id FK ; created_by_id FK ; status ; ordered_at ; received_at |
| purchase_item | PK id ; purchase_id FK ; ingredient_id FK ; quantity ; unit ; unit_price DECIMAL(12,4) |
| stock_movement | PK id ; ingredient_id FK ; author_id FK ; type ; quantity ; unit ; reason ; occurred_at ; source_type ; source_id ; unit_cost DECIMAL(12,4) |
| inventory | PK id ; author_id FK ; status ; started_at ; closed_at |
| inventory_item | PK id ; inventory_id FK ; ingredient_id FK ; theoretical_quantity ; counted_quantity ; variance |
| restaurant_order | PK id ; waiter_id FK ; table_number ; status ; created_at ; served_at ; cancelled_at |
| order_item | PK id ; order_id FK ; dish_id FK ; quantity ; unit_sale_price_snapshot DECIMAL(10,2) ; status ; stock_deducted_at |
| menu | PK id ; name ; description ; sale_price DECIMAL(10,2) ; is_active |
| menu_item | PK id ; menu_id FK ; dish_id FK ; course ; is_choice |
| product_reference | PK id ; ingredient_id FK ; barcode UNIQUE ; product_name ; allergens JSON ; nutrition_data JSON ; fetched_at ; source |

Les colonnes `source_type/source_id` de `stock_movement` servent à retrouver l'événement métier d'origine. L'idempotence doit être renforcée par une contrainte unique dédiée (par exemple `source_type, source_id, ingredient_id, movement_type`) pour les consommations liées à une ligne de commande.
