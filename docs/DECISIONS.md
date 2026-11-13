# Journal des décisions

## 2026-11-13 — Cadrage initial
Sujet officiel retenu : « Gestion d'un restaurant : carte, cuisine et stocks ». Le challenge prévoit cinq niveaux cumulatifs et valorise un socle solide, les tests, les décisions justifiées et un historique Git sincère.

### Questions métier du sujet EMC

**1. Le coût d'un plat déjà servi change-t-il si le prix d'achat évolue ?**  
Non. Chaque mouvement de consommation conserve le coût unitaire appliqué au moment de la sortie ; les achats suivants utilisent leur nouveau prix. Le coût historique d'un plat servi est donc stable. Pour les coûts courants, on calcule le coût de recette avec le coût unitaire courant (ou coût moyen pondéré si cette méthode est implémentée et documentée).

**2. Comment enregistrer les pertes ?**  
Créer un mouvement de stock de type `LOSS` avec quantité positive représentant l'amplitude de la sortie, motif obligatoire (`EXPIRY`, `BREAKAGE`, `PREPARATION_ERROR`, `OTHER`), commentaire facultatif, auteur et horodatage. La quantité disponible diminue dans la même transaction.

**3. Un menu « entrée + plat + dessert au choix » est-il un plat comme les autres ?**  
Non. Le menu est un agrégat commercial composé de choix/étapes, tandis que chaque plat garde sa recette et sa consommation matière. Le prix du menu est distinct du prix individuel. Lors de la commande, les choix concrets sont enregistrés afin de calculer et consommer les bonnes recettes. Un menu ne doit pas avoir une recette unique artificielle.

**4. Le stock peut-il devenir négatif ?**  
Non. Toute sortie verrouille et vérifie le stock dans une transaction ; elle échoue si la quantité est insuffisante. Un ajustement d'inventaire ne peut pas contourner cette règle sans validation explicite et traçabilité.

**5. Que faire si l'information allergène est absente ou incohérente ?**  
Afficher « inconnue / à vérifier », conserver la source et la date de récupération, et ne jamais interpréter une absence de données comme une absence d'allergènes. En cas de contradiction, signaler la donnée comme non fiable et demander une vérification humaine.

### Autres décisions
- Décrémentation au statut `SERVED`, conformément au cahier des charges. `READY` ne consomme pas encore le stock.
- Consommation atomique par ligne servie et idempotente ; la clé de la ligne de commande est associée aux mouvements.
- Une annulation avant service ne consomme pas. Une correction après service crée un mouvement de régularisation, jamais une suppression de l'historique.
- Unités canoniques : g, ml, pièce. Conversion kg→g et L→ml ; pas de conversion implicite entre masse et volume.
- Prix de vente snapshotés dans `OrderItem`, prix matière snapshotés dans `StockMovement`.
- Symfony 7.4 en monolithe modulaire ; pas de microservices pour un seul restaurant et une fenêtre de sept jours.
- Les comptes de démonstration et secrets ne seront ajoutés qu'avec fixtures et variables locales non versionnées.

## Risques et mesures
- **Course concurrente sur le stock** : verrouillage pessimiste et test fonctionnel multi-transaction si possible.
- **Double décrémentation** : contrainte d'unicité/idempotence et transition d'état contrôlée.
- **Arrondis** : DECIMAL en base et calculs décimaux maîtrisés.
- **Recettes imbriquées** : détection des cycles de préparations.
- **Allergènes incomplets** : état explicite inconnu et validation humaine.
