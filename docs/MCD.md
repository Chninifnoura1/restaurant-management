# Modèle conceptuel de données (MCD)

## Entités et attributs principaux
- **User**(id, email, passwordHash, displayName, isActive)
- **Role**(id, code, label) — rôles applicatifs attribués aux utilisateurs.
- **Category**(id, name, description)
- **Dish**(id, categoryId, recipeId, name, description, salePrice, isActive, archivedAt)
- **Ingredient**(id, name, baseUnit, currentQuantity, minimumQuantity, isActive)
- **Recipe**(id, dishId, name, yieldQuantity, instructions)
- **RecipeIngredient**(id, recipeId, ingredientId, quantity, unit)
- **Preparation**(id, name, yieldQuantity, yieldUnit, instructions)
- **PreparationIngredient**(id, preparationId, ingredientId, quantity, unit)
- **RecipePreparation**(id, recipeId, preparationId, quantity, unit)
- **Supplier**(id, name, contact, email, phone)
- **SupplierIngredient**(id, supplierId, ingredientId, supplierReference, lastPurchasePrice)
- **Purchase**(id, supplierId, createdById, status, orderedAt, receivedAt)
- **PurchaseItem**(id, purchaseId, ingredientId, quantity, unit, unitPrice)
- **StockMovement**(id, ingredientId, authorId, type, quantity, unit, reason, occurredAt, sourceType, sourceId, unitCost)
- **Inventory**(id, authorId, status, startedAt, closedAt)
- **InventoryItem**(id, inventoryId, ingredientId, theoreticalQuantity, countedQuantity, variance)
- **RestaurantOrder**(id, waiterId, tableNumber, status, createdAt, servedAt, cancelledAt)
- **OrderItem**(id, orderId, dishId, quantity, unitSalePriceSnapshot, status, stockDeductedAt)
- **Menu**(id, name, description, salePrice, isActive)
- **MenuItem**(id, menuId, dishId, course, isChoice)
- **ProductReference**(id, ingredientId, barcode, productName, allergens, nutritionData, fetchedAt, source)

## Associations et cardinalités
- Category 1 — 0..N Dish ; chaque Dish appartient à 1 Category.
- Dish 1 — 1 Recipe ; une recette est la composition matière d'un plat.
- Recipe 1 — 0..N RecipeIngredient ; chaque ligne référence 1 Ingredient.
- Recipe 1 — 0..N RecipePreparation ; chaque ligne référence 1 Preparation.
- Preparation 1 — 0..N PreparationIngredient ; chaque ligne référence 1 Ingredient.
- Ingredient N — N Supplier via SupplierIngredient.
- Supplier 1 — 0..N Purchase ; Purchase 1 — 1..N PurchaseItem.
- Ingredient 1 — 0..N StockMovement.
- Inventory 1 — 1..N InventoryItem ; Ingredient 1 — 0..N InventoryItem.
- User 1 — 0..N RestaurantOrder comme serveur ; RestaurantOrder 1 — 1..N OrderItem.
- Dish 1 — 0..N OrderItem.
- Menu 1 — 1..N MenuItem ; Dish 1 — 0..N MenuItem.
- Ingredient 1 — 0..N ProductReference (historique éventuel des correspondances).
- User N — N Role (ou rôle unique par utilisateur si simplification explicitée).

## Contraintes d'intégrité
- Quantités strictement positives sur lignes de recette, achat et commande.
- Montants DECIMAL(10,2) ou DECIMAL(12,4) selon usage ; pas de float PHP pour les montants.
- Unicité de `User.email`, `Role.code`, `Ingredient.name` (selon règle de nommage), `ProductReference.barcode`.
- Stock courant >= 0, contrôlé en base et dans le service transactionnel.
- `OrderItem.stockDeductedAt` et une clé source idempotente empêchent la double consommation.
- Les lignes de mouvement sont append-only ; une correction produit un mouvement inverse/ajustement.
- Prix de vente et prix d'achat sont copiés sur les lignes concernées pour préserver l'historique.
