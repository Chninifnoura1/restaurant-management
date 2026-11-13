# Note conceptuelle — PFE Challenge EMC
## Gestion d'un restaurant : carte, cuisine et stocks

Le choix du sujet Restaurant répond à un problème concret : dans un établissement de 60 couverts, les commandes papier et le suivi manuel des ingrédients rendent les échanges salle-cuisine fragiles, favorisent les ruptures et le gaspillage, et empêchent de connaître précisément la marge des plats.

L'analyse a consisté à reprendre le parcours opérationnel complet : création de la carte, définition des recettes, prise de commande, préparation, service, consommation des ingrédients, réception des achats et inventaire. Les besoins ont été hiérarchisés selon les cinq niveaux du challenge EMC. Le modèle distingue les plats vendus, les menus composés, les recettes, les préparations réutilisables, les ingrédients, les mouvements de stock et les commandes.

L'architecture choisie est un monolithe modulaire Symfony 7.4 avec Doctrine et MySQL 8. Les contrôleurs restent minces ; les opérations sensibles sont centralisées dans des services métier transactionnels. Les quantités sont normalisées par dimension et les montants utilisent des décimaux. Le stock est décrémenté au service effectif du plat, avec verrouillage, contrôle du disponible et idempotence pour éviter les doubles sorties. Les prix historiques sont conservés afin qu'une variation ultérieure des achats ne réécrive pas le coût d'un plat déjà servi.

Le développement est ordonné par risque : conception et modèle, CRUD et données de démonstration, règles de stock et tests, rôles et écrans opérationnels, intégration Open Food Facts, puis Docker et installation reproductible. Cette progression privilégie un noyau cohérent plutôt qu'une accumulation d'écrans incomplets.

L'intelligence artificielle est utilisée comme assistant de conception et de production : clarification du besoin, proposition de modèle, rédaction initiale de documentation et aide à la structuration. Ses propositions doivent être confrontées au sujet officiel, relues, exécutées et couvertes par des tests ; aucune fonctionnalité n'est déclarée terminée sur la seule base d'un texte généré. Les échanges et corrections significatifs seront conservés dans les traces IA, après retrait des secrets.

Avec une semaine supplémentaire, les améliorations prioritaires seraient le suivi des lots et dates limites, une gestion plus fine des menus à choix, des alertes de seuil configurables, un écran cuisine temps réel via Mercure, des rapports de marge plus détaillés et des tests de concurrence sur les sorties de stock.
