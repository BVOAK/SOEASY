# Cahier des charges - Finalisation du Configurateur SoEasy

## 📅 Date : 2025-07-11

---

## ✅ Fonctionnalités finalisées

### Étape 2 : Internet
- [x] Affichage conditionnel des blocs matériels et secours selon le forfait sélectionné.
- [x] Affichage immédiat du bloc dans la sidebar dès qu’un forfait est coché.
- [x] Mise à jour dynamique de `updateEngagementVisibility()` selon forfait ou leasing.
- [x] Enregistrement des forfaits et matériels dans le localStorage.
- [x] Suppression automatique des frais d'installation si un forfait est décoché.
- [x] Rafraîchissement de la sidebar sans recharger la page.

### Étape 3 : Mobile
- [x] Affichage dynamique des produits avec checkbox + quantité.
- [x] Ajout/remplacement dans le localStorage + envoi session.
- [x] Nettoyage partiel de `initStep3Events` (sans refonte complète).

### Étape 4 : Centrex
- [x] Traitement unifié des frais d'installation :
  - FI Licences → quantité toujours 1 (gratuite si seuil atteint).
  - FI Postes → une ligne unique dans LS.
  - FI Matériel → fusion et incrément des quantités par ID.
- [x] Mise à jour de la fonction `saveCentrexQuantites()`.
- [x] Envoi AJAX en session dès ajout ou modification.
- [x] Correction des problèmes de doublons FI.

### Étape 5 : Frais d'installation
- [x] Refonte complète de `initStep5Events` :
  - Plus d’objet `{ reporte: true }` → logique simplifiée avec tableau vide.
  - Restauration dynamique propre sans erreurs.
- [x] Affichage correct des prix comptant/leasing.
- [x] Résolution du bug d'affichage aléatoire au chargement.
- [x] Affichage dynamique du total global.
- [x] Désactivation de la case "report" si un FI est coché.
- [x] Forçage à 0 € si tous les FI décochés.

### Étape 6 : Récapitulatif
- [x] Recalcul des prix comptant/leasing.
- [x] Affichage dynamique des blocs par adresse.
- [x] Correction de `updateRecapitulatif()` pour FI.
- [x] Nettoyage des appels en double.

### Core JS
- [x] Mise à jour de `saveToLocalConfig()` :
  - Gère tous les cas proprement.
  - Envoie en session WooCommerce.
- [x] Mise à jour de `updateSidebarProduitsRecap()` pour afficher les bonnes adresses.
- [x] Correction `soeasyConfig` pour `btn-remove-adresse`.

---

## 🐞 Bugs corrigés

- [x] FI non visibles sans rechargement → fixé avec injection forcée.
- [x] Prix des FI restaient en comptant → mise à jour avec `updatePrices`.
- [x] Forfait décoché mais réapparaît → fix de `initStep2Events`.
- [x] Sidebar vide au chargement de l'étape → réinitialisation forcée.
- [x] Doublons de prix affichés dans FI → structure DOM corrigée.
- [x] Cas "report" qui ajoutait un objet invalide dans le LS → remplacé par tableau vide.

---

## 🚧 Ce qu’il reste à faire

### À court terme
- [ ] Vérifier les appels à `saveToLocalConfig()` dans toutes les étapes.
- [ ] Créer une fonction `sendToCart()` JS pour valider et envoyer vers `soeasy_ajouter_au_panier`.
- [ ] Ajouter le bouton "Valider la configuration" avec `sendToCart()` dans step-6.
- [ ] Vérifier et afficher les erreurs PHP (logs 500) s’il manque des produits en session.

### À moyen terme
- [ ] Refonte propre de `initStep3Events` (mobile) avec simplification comme étape 4.
- [ ] Nettoyage du code `updateSidebarProduitsRecap` et `updatePrixProduits`.
- [ ] Optimisation de la structure `localStorage` pour multi-adresses.

---

## 🧩 À valider avec l’utilisateur
- Fonction de validation → redirection automatique vers le checkout avec tous les produits.
- Rendu du panier WooCommerce (présentation des lignes).
- Cas “Sans Engagement” sur forfaits Internet à gérer dès qu’activé.
