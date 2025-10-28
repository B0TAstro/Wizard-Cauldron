# Wizard Cauldron — Jeu d’Opening & Collection _(Brouillon)_

> **Statut** : brouillon pédagogique • **Cible d’apprentissage** = **Symfony**, **Formulaires**, **Session**.  
> **Durée** : ~2 semaines (**2 jours full** + **soirées**).  
> **DA** : vibe “iOS clean” + fun **YapYap-like** (icônes rondes, micro-animations, feedbacks “juicy”).

## 🎯 Objectifs d’apprentissage
- Prendre en main **Symfony** : routing, contrôleurs, vues **Twig**.
- Manipuler des **Formulaires** (Form Types, CSRF, Validator).
- Gérer de l’état côté serveur via **Session** (monnaie/journalier, inventaire local).
- Mettre en place une **authentification** simple (user/admin) et protéger des routes.
- Persister en base (**Doctrine**) et faire un **CRUD admin**.

## 🧾 Brief
Un **mini-jeu d’ouverture** : chaque jour, un utilisateur reçoit **1 pièce**.  
Il clique sur le **chaudron** → **animation** → tirage d’un **sort** (image, nom, rareté, description).  
Une page **Collection** liste tous les sorts : ceux **débloqués** s’affichent normalement ; les **verrouillés** montrent seulement **rareté**, **icône silhouette** (noir) et **description obfusquée**.  
Côté **admin**, on gère le **catalogue de sorts** et les **utilisateurs**.

---

## 🕹️ Gameplay (côté user)
- **Inscription / Connexion** (email + mot de passe).
- **Daily coin** : 1 pièce/jour (réinitialisation à J+1).  
- **Chaudron** `/cauldron` :
  - si le user a ≥1 pièce → bouton **“Invoquer”** active l’anim, décrémente la pièce, retourne un sort selon les **probabilités de rareté** (config).
  - si 0 pièce → message “reviens demain” + timer J+1.
- **Collection** `/collection` :
  - grille de cartes (tous les sorts).  
  - **Débloqués** : image, nom, rareté, description lisible.  
  - **Lock** : image en **silhouette** (noir), **nom** + **description** **obfusqués** (ex: substitution caractères).  
  - Filtres par **rareté** et **état** (débloqué/lock).
- **Détail sort** `/spell/{slug}` (optionnel) : visuel XL + lore.

### Raretés (proposition par défaut)
- **Common** 60% • **Rare** 25% • **Epic** 10% • **Legendary** 5%  
_(poids configurables en env ou table `rarity`)_

---

## 🛠️ Admin
- **CRUD Sorts** : lister / créer / éditer / supprimer. Champs : nom, slug, rareté, image (URL ou upload local), description, **isActive**.  
- **Utilisateurs** : lister, **compteur** “sorts débloqués / total”, **supprimer** un user (soft delete ou hard delete).  
- **Paramètres (optionnel)** : poids de rareté, reset automatisé des pièces.

---

## 🗂️ Modèle de données (minimal)
```text
User
- id (PK)
- email (unique), password (hash), roles (json)    # ROLE_USER, ROLE_ADMIN
- coins (int)                                      # pièces actuelles
- lastDailyAt (datetime_immutable|null)            # dernière récupération J/N
- createdAt (datetime_immutable)

Spell
- id (PK)
- name (string 120), slug (string 160 unique)
- rarity (enum/string: common|rare|epic|legendary)
- imageUrl (string|null)
- description (text)
- isActive (bool, default true)
- createdAt (datetime_immutable)

UserSpell (obtention d’un sort par un user)
- id (PK)
- user (ManyToOne -> User, index)
- spell (ManyToOne -> Spell, index)
- obtainedAt (datetime_immutable)
- UNIQUE(user, spell)                              # pas de doublon dans la collection
```

> _Optionnel_ : table `RarityWeight` si tu veux éditer les probabilités côté admin.

---

## 🌐 Routes (brouillon)
- **Public** :  
  - `GET /` → landing (cta vers login/cauldron/collection)
  - `GET|POST /register`, `GET|POST /login`, `POST /logout`
- **User** :  
  - `GET /cauldron` → UI chaudron + état (pièces, prochain daily)  
  - `POST /cauldron/open` → **Formulaire** CSRF (décrémenter pièce + RNG sort + `UserSpell`)  
  - `GET /collection` → grille (débloqués/lock, filtres)  
  - `GET /spell/{slug}` (optionnel)  
  - `POST /daily/claim` → récupère **1 pièce** si eligible (idempotent)
- **Admin** (protégé `ROLE_ADMIN`) :  
  - `GET /admin` → dashboard simple  
  - `GET|POST /admin/spells/new`, `GET|POST /admin/spells/{id}/edit`, `POST /admin/spells/{id}/delete`, `GET /admin/spells`  
  - `GET /admin/users`, `POST /admin/users/{id}/delete`

---

## 🧩 Comportements & Session
- **Session** : utilisée pour le **feedback d’ouverture** (dernier tirage, messages flash), et éventuellement pour la **collection locale** côté invité (si tu supportes un “mode guest”).  
- **Règle daily** : `POST /daily/claim` vérifie `lastDailyAt` (>= jour courant ?), puis `coins++` et met `lastDailyAt=now`.  
- **Ouverture** : si `coins > 0` → `coins--`, tirage **pondéré** sur `rarity`, choisir un `Spell` **actif** de cette rareté. Insérer `UserSpell` si inexistant.  
- **Obfuscation** : utilitaire Twig/Service pour **masquer** nom/desc (ex: remplace lettres par symboles) quand non débloqué.

---

## 🔐 Sécurité
- Auth **form_login** (Symfony Security), hashing **auto** via `password_hasher`.  
- **Voters**/contrôles simples pour les actions admin.  
- CSRF pour tous les **POST** (ouverture, daily, CRUD).

---

## 🧪 DoD & Tests manuels
- Daily : impossible de “gratter” plusieurs fois la même journée.  
- Ouverture : décrémente une pièce, persiste un tirage, affiche le résultat.  
- Collection : vue complète, **verrouillés obfusqués**.  
- Admin : CRUD Spells OK ; liste users avec **X/Y** débloqués ; suppression user OK.  
- Messages flash clairs (succès/erreurs), empty states, 404/403 propres.

---

## 🗺️ Roadmap (indicative)
- **Jour Full 1** : Setup (Symfony, Security), entités `User`, `Spell`, `UserSpell` + migrations, fixtures `Spell`, pages `/`, `/login`, `/register`.  
- **Jour Full 2** : Daily coin + **cauldron open** (service RNG + session feedback) + **Collection** (obfuscation).  
- **Soirs (S1)** : Admin CRUD Spells + listing Users avec compteur.  
- **Soirs (S2)** : Polish UI (chaudron animé), filtre Collection, 404/403, petites validations & flash.

> _Si timing serré_ : repousser `/spell/{slug}`, suppression user, poids éditables.

---

## 🔧 Setup (mémo rapide)
```bash
symfony new wizard-cauldron --webapp
cd wizard-cauldron

composer require orm maker twig form validator security annotations symfony/asset
# (optionnel) fixtures pour peupler des sorts
composer require --dev orm-fixtures fakerphp/faker

# DB
# -> configure DATABASE_URL dans .env.local
php bin/console doctrine:database:create
php bin/console make:entity User
php bin/console make:entity Spell
php bin/console make:entity UserSpell
php bin/console make:migration && php bin/console doctrine:migrations:migrate

# Security (login)
php bin/console make:user
php bin/console make:auth

# Admin CRUD
php bin/console make:crud Spell
```
*(tu compléteras les champs selon le modèle plus haut)*

---

## 🖼️ UI / DA (brouillon)
- **Chaudron** centré, bouton “Invoquer” → **animation pop** (scale, particules), secousse légère.  
- **Cartes Sort** : format carré, coins 20–24, fond verre dépoli.  
- **Rareté** → couleur subtile (Common gris, Rare bleu, Epic violet, Legendary or), halo léger.  
- **Lock** → image silhouette noir + texte obfusqué.  
- **Micro-animations** iOS-like (hover, press, feedback).

---

## 🧱 Noms potentiels du projet
- **Wizard Cauldron**
- **Arcana Forge**
- **Hex & Hoard**
- **Charmed Crucible**
- **Mystic Brew**
- **Spellmint**
- **Sorcerer’s Stash**
- **Runic Vault**
- **Cauldron Click**
- **Wizardry Daily**

---

## ✅ TODO (checklist)
- [ ] Entités + migrations + fixtures Spell.  
- [ ] Auth user/admin.  
- [ ] Daily coin (route + logique + garde-fou).  
- [ ] Ouverture chaudron (POST + service RNG + session + UI).  
- [ ] Collection (grille + obfuscation + filtres).  
- [ ] Admin CRUD Spells.  
- [ ] Liste Users + compteur X/Y + suppression (si temps).  
- [ ] Polish UI + messages flash + 404/403.

---

### Licence
À définir (MIT par défaut).
