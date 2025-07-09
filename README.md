# SOEASY

# SOEASY – Configurateur Télécom B2B

Ce dépôt contient le code du configurateur SoEasy, plateforme e-commerce B2B pour les offres de télécommunication (Internet, Mobile, Centrex).

## ⚙️ Stack technique

- WordPress + WooCommerce
- JavaScript personnalisé (jQuery)
- ACF Pro pour les tarifs dynamiques
- PHP custom (sans extensions de bundles WooCommerce)
- Stockage : localStorage + session WooCommerce

## 📁 Structure

- `configurateur.js` : gestion des étapes, navigation, UI
- `configurateur-fonctions.js` : calculs dynamiques, prix, stockage
- `step-1.php` → `step-6.php` : étapes du tunnel de configuration
- `functions-configurateur.php` : AJAX, session, injection panier

## 🚧 TODO

- Intégration API d’éligibilité
- Finalisation étape 5 et 6
- Tunnel WooCommerce
- Responsive + UX

---

© BVOAK – Projet confidentiel
