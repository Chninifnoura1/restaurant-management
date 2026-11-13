# Diagrammes de séquence

## Création et transmission d'une commande
```mermaid
sequenceDiagram
actor Serveur
participant UI
participant OrderController
participant OrderService
participant AvailabilityService
participant DB
Serveur->>UI: Sélectionne table et plats
UI->>OrderController: POST commande
OrderController->>OrderService: createOrder(data)
OrderService->>AvailabilityService: assertAvailable(dishes, quantities)
AvailabilityService->>DB: Lire recettes et stocks
DB-->>AvailabilityService: Disponibilités
AvailabilityService-->>OrderService: OK
OrderService->>DB: Transaction : commande + lignes
DB-->>OrderService: Commit
OrderService-->>UI: Commande créée
```

## Transmission et préparation en cuisine
```mermaid
sequenceDiagram
actor Serveur
participant OrderService
participant DB
actor Cuisine
Serveur->>OrderService: confirmOrder(order)
OrderService->>DB: Statut CONFIRMED
Cuisine->>DB: Consulte les commandes actives
Cuisine->>DB: IN_PREPARATION puis READY
```

## Validation du plat servi et décrémentation
```mermaid
sequenceDiagram
actor Serveur
participant OrderController
participant OrderService
participant StockService
participant DB
Serveur->>OrderController: Marquer ligne SERVED
OrderController->>OrderService: markItemServed(itemId)
OrderService->>DB: BEGIN
OrderService->>StockService: consumeRecipe(item, idempotencyKey)
StockService->>DB: Verrouiller ingrédients (FOR UPDATE)
StockService->>StockService: Convertir unités et vérifier quantités
alt Stock suffisant et non déjà consommé
  StockService->>DB: Insérer mouvements OUT
  StockService->>DB: Mettre à jour stocks
  OrderService->>DB: Marquer ligne SERVED + stockDeductedAt
  OrderService->>DB: COMMIT
else Insuffisant ou déjà consommé
  OrderService->>DB: ROLLBACK / réponse idempotente
end
```

## Réapprovisionnement d'un ingrédient
```mermaid
sequenceDiagram
actor Gérant
participant PurchaseController
participant PurchaseService
participant DB
Gérant->>PurchaseController: Réceptionner achat
PurchaseController->>PurchaseService: receive(purchaseId)
PurchaseService->>DB: BEGIN + verrouiller achat
PurchaseService->>DB: Créer mouvements IN avec prix d'achat
PurchaseService->>DB: Mettre à jour quantités et statut reçu
PurchaseService->>DB: COMMIT
```
