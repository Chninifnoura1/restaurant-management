# Modèle de données — synthèse

Le modèle sépare le catalogue commercial (plats et menus), la composition matière (recettes et préparations), les approvisionnements (fournisseurs et achats), le stock (mouvements et inventaires) et l'activité de salle (commandes et lignes).

```mermaid
erDiagram
  CATEGORY ||--o{ DISH : classe
  DISH ||--|| RECIPE : possede
  RECIPE ||--o{ RECIPE_INGREDIENT : contient
  INGREDIENT ||--o{ RECIPE_INGREDIENT : compose
  RECIPE ||--o{ RECIPE_PREPARATION : utilise
  PREPARATION ||--o{ RECIPE_PREPARATION : reutilisee
  PREPARATION ||--o{ PREPARATION_INGREDIENT : contient
  INGREDIENT ||--o{ PREPARATION_INGREDIENT : compose
  SUPPLIER ||--o{ PURCHASE : recoit
  PURCHASE ||--|{ PURCHASE_ITEM : detaille
  INGREDIENT ||--o{ PURCHASE_ITEM : achete
  INGREDIENT ||--o{ STOCK_MOVEMENT : trace
  INVENTORY ||--|{ INVENTORY_ITEM : compte
  INGREDIENT ||--o{ INVENTORY_ITEM : concerne
  USER ||--o{ RESTAURANT_ORDER : prend
  RESTAURANT_ORDER ||--|{ ORDER_ITEM : contient
  DISH ||--o{ ORDER_ITEM : commande
  MENU ||--|{ MENU_ITEM : compose
  DISH ||--o{ MENU_ITEM : propose
```

Les détails, attributs, cardinalités et contraintes figurent dans [MCD.md](MCD.md) et [MLD.md](MLD.md).
