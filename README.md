# Wizard Cauldron — Jeu d’Opening & Collection _(Brouillon)_

> **Statut** : **Cible d’apprentissage** = **Symfony**, **Formulaires**, **Session** et **Database**
> **Durée** : ~3 semaines 

## 🎯 Objectifs d’apprentissage
- Prendre en main **Symfony** : routing, contrôleurs et vues **Twig**
- Manipuler des **Formulaires**
- Mettre en place une **authentification** (user/admin) et protéger des routes
- Gérer de l’état côté serveur via **Session** (monnaie/journalier, inventaire local)

## 🧾 Brief
Un **mini-jeu d’ouverture** : chaque jour, un utilisateur reçoit **1 pièce** !
Il clique sur un **chaudron** → **animation** → tirage d’un **sort**

Il a accès à une page **Collection** qui liste tous les sorts : ceux **débloqués** s’affichent normalement, les **verrouillés** montrent seulement la **rareté**, une **silhouette** de l'image du sort et la **description et le titre masqué**

Côté **admin**, on gère le **catalogue de sorts** et les **utilisateurs**

---

## 🕹️ Gameplay (côté user)
- **Inscription / Connexion** (email + mot de passe)
- **Daily coin** : 1 pièce/jour
- **Chaudron** `/cauldron` :
  - si le user a ≥1 pièce → cliquer sur le Chaudron active l’anim (décrémente la pièce) et retourne un sort selon les **probabilités de rareté**
  - si 0 pièce → message “reviens demain”
- **Collection** `/collection` :
  - grille de cartes (tous les sorts)
    - **Débloqués** : image, nom, rareté et description
    - **Lock** : image en **silhouette**, **nom** + **description et le titre masqué**
    - Filtres par **rareté** et **état** (débloqué/lock) (optionnel)

### Raretés (proposition par défaut)
- **Common** 60% • **Rare** 25% • **Epic** 10% • **Legendary** 5%  

---

## 🛠️ Admin
- **Sorts** : lister / créer / éditer / supprimer. Champs: nom, slug, rareté, image et description
- **Utilisateurs** : lister, **compteur** “sorts débloqués / total”, **supprimer** un user (hard delete)
- **Paramètres (optionnel)** : poids de rareté, reset automatisé des pièces

---

## 🖼️ UI / DA
- **Chaudron** centré, bouton caché dans le chaudron → **animation pop** (scale, particules), secousse légère
- **Cartes Sort** : rectangle, border qui indique la rareté, icon/image à gauche et le titre/description à gauche
  - **Rareté** → couleur subtile (Common gris, Rare bleu, Epic violet, Legendary or), halo léger

---

## 🗂️ Modèle de données
```text
User
- id (PK)
- email (unique), password (hash), roles (json)    # ROLE_USER, ROLE_ADMIN
- coins (int)
- lastDailyAt (datetime_immutable|null)
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
- UNIQUE(user, spell)
```
> (Optionnel) : table `RarityWeight` si tu veux éditer les probabilités côté admin.

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
  - `POST /daily/claim` → récupère **1 pièce** si eligible
- **Admin** (protégé `ROLE_ADMIN`) :  
  - `GET /admin` → dashboard simple  
  - `GET|POST /admin/spells/new`, `GET|POST /admin/spells/{id}/edit`, `POST /admin/spells/{id}/delete`, `GET /admin/spells`  
  - `GET /admin/users`, `POST /admin/users/{id}/delete`

---

## 🔐 Sécurité
- Auth **form_login** (Symfony Security), hashing **auto** via `password_hasher`.  
- **Voters**/contrôles simples pour les actions admin.  
- CSRF pour tous les **POST** (ouverture, daily, CRUD).

---

## ✅ TODO
- [X] **Modèle & DB**
  - [X] Entités `User`, `Spell`, `UserSpell` + **migrations**
- [ ] **Auth & Formulaires**
  - [ ] **Register/Login** (form_login) + validation
  - [ ] Formulaires admin (création/édition Spell)
- [ ] **Vues Admin**
  - [ ] CRUD Spells (lister / créer / éditer / supprimer)
  - [ ] Liste Users avec compteur **débloqués/total** + suppression
- [ ] **Vues User**
  - [ ] Landing `/`
  - [ ] **Collection** (grille, lock obfusqué, filtres optionnels)
- [ ] **Gameplay**
  - [ ] Daily coin (`/daily/claim`) avec garde-fou jour courant
  - [ ] **Cauldron open** (`/cauldron/open`) : décrément, RNG pondérée, persist `UserSpell`, feedback session
- [ ] **Polish (optionnel)**
  - [ ] UI/DA (anim chaudron, cartes rareté, empty states)
  - [ ] 404/403 propres, messages flash cohérents
  - [ ] Paramétrage poids rareté en base/env

---

## 🗺️ Roadmap (révisée)
- **Semaine 1 — Modèle & Auth**
  - Entités + migrations + fixtures Spell
  - Register/Login (+ garde routes protégées)
  - Squelettes de templates (layout, nav, flash)
- **Semaine 2 — Admin d’abord**
  - CRUD Spells complet
  - Liste Users + compteur X/Y + suppression
- **Semaine 3 — User & Gameplay**
  - Collection (lock/obfuscation), landing
  - Daily coin + Cauldron open (RNG pondérée + session feedback)
  - Polish léger (messages, petites animations)

---

### Licence
À définir (MIT par défaut).