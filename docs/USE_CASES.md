# Diagramme de cas d'utilisation

```mermaid
flowchart LR
  Manager[ Gérant ]
  Waiter[ Serveur ]
  Kitchen[ Cuisine ]

  subgraph App[Restaurant Management]
    U1((Gérer utilisateurs et rôles))
    U2((Gérer catégories et plats))
    U3((Gérer recettes et préparations))
    U4((Gérer ingrédients et fournisseurs))
    U5((Créer et réceptionner achats))
    U6((Réaliser inventaire))
    U7((Consulter alertes et mouvements))
    U8((Consulter dashboard et marges))
    U9((Créer commande))
    U10((Affecter table et serveur))
    U11((Transmettre commande en cuisine))
    U12((Consulter file de commandes))
    U13((Démarrer préparation))
    U14((Marquer plat prêt))
    U15((Marquer plat servi))
    U16((Signaler problème))
  end

  Manager --> U1
  Manager --> U2
  Manager --> U3
  Manager --> U4
  Manager --> U5
  Manager --> U6
  Manager --> U7
  Manager --> U8
  Waiter --> U9
  Waiter --> U10
  Waiter --> U11
  Waiter --> U7
  Kitchen --> U12
  Kitchen --> U13
  Kitchen --> U14
  Kitchen --> U15
  Kitchen --> U16
  U9 -. vérifie disponibilité .-> U4
  U15 -. déclenche consommation .-> U7
  U5 -. crée entrée de stock .-> U7
  U6 -. crée ajustement .-> U7
```
