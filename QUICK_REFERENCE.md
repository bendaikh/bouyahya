# 🎯 Quick Reference: What Can Each User See?

## 👤 Commercial User (comm@gmail.com)

### ✅ CAN SEE (SHARED)
- **All 96 Clients** - Can select any client when creating orders
- **All Fournisseurs** - Complete list available
- **All Articles** - Inventory is shared

### 🔒 CANNOT SEE (SCOPED)
- Other users' Bon de Commandes
- Other users' Bon de Livraisons
- Other users' Règlements

### 📊 Dashboard Shows
- Only THEIR sales
- Only THEIR orders
- Only THEIR statistics

---

## 👤 Assistant User (souad@gmail.com)

### ✅ CAN SEE (SHARED)
- **All 96 Clients** - Same as Commercial
- **All Fournisseurs** - Same as Commercial
- **All Articles** - Shared inventory

### 🔒 CANNOT SEE (SCOPED)
- Other users' Bon d'Achats
- Other users' Règlements Fournisseurs
- Commercial's Bon de Commandes

### 📊 Dashboard Shows
- Only THEIR purchases
- Only THEIR statistics

---

## 👑 Superadmin (admin@bouyahya.com)

### ✅ SEES EVERYTHING
- All 96 Clients
- All Fournisseurs
- **ALL 39 Bon de Commandes** (from all users)
- **ALL Bon de Livraisons** (from all users)
- **ALL Règlements** (from all users)

### 📊 Dashboard Shows
- **Company-wide totals**
- All users' combined statistics

---

## 🎨 Visual Representation

```
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE STRUCTURE                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  SHARED RESOURCES (🌍 Visible to ALL)                       │
│  ├── Clients (96)                                           │
│  │   └── Everyone sees all 96                              │
│  ├── Fournisseurs (50)                                      │
│  │   └── Everyone sees all 50                              │
│  └── Articles                                               │
│      └── Shared inventory                                   │
│                                                              │
│  SCOPED RESOURCES (🔒 User-specific)                        │
│  ├── Bon de Commandes (39 total)                           │
│  │   ├── Admin's: 20 ← Only Admin & Superadmin see        │
│  │   ├── Commercial's: 10 ← Only Commercial & Superadmin  │
│  │   └── Assistant's: 9 ← Only Assistant & Superadmin     │
│  │                                                          │
│  ├── Bon de Livraisons                                     │
│  │   └── Each user sees only theirs                        │
│  │                                                          │
│  └── Règlements                                             │
│      └── Each user sees only theirs                        │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 Common Actions & Results

### Action: Create New Bon de Commande

**As Commercial User:**
1. Click "Nouveau Bon de Commande"
2. **Client Dropdown**: ✅ Shows all 96 clients
3. **Fournisseur Dropdown**: ✅ Shows all fournisseurs
4. Fill form and save
5. **Result**: Order created with `user_id = 3` (Commercial)

**As Another User:**
- Cannot see the Commercial's order
- Can create their own with same clients

**As Superadmin:**
- Sees the Commercial's order in the list
- Sees all users' orders

---

### Action: View Clients List

**Any User (Commercial, Assistant, etc.):**
- **URL**: `/clients`
- **Sees**: All 96 clients
- **Can**: Create new clients
- **Result**: New client visible to ALL users immediately

**Superadmin:**
- Same as regular users (sees all 96 clients)

---

### Action: View Dashboard

**Commercial User:**
```
Dashboard Stats:
- Total Ventes: 50,000 DH (only THEIR sales)
- Total Clients: 96 (shared count)
- Recent Orders: Only THEIR orders
- Solde: Only THEIR client balances
```

**Superadmin:**
```
Dashboard Stats:
- Total Ventes: 250,000 DH (ALL users combined)
- Total Clients: 96 (same)
- Recent Orders: From ALL users
- Solde: Company-wide balances
```

---

## ❓ FAQ

### Q: Why can Commercial see all clients but not all orders?
**A**: Clients are company resources (like a phone book). Everyone needs access to create orders. But orders are personal work items - each user manages their own.

### Q: What if two users work with the same client?
**A**: Perfect! Both users can create orders for that client. Each order belongs to the user who created it.

### Q: Can users see who created a client?
**A**: The `user_id` is stored in the database, but in the UI, all users just see "Clients". The creator info is tracked for audit purposes.

### Q: What happens to existing data?
**A**: All existing clients, fournisseurs, and orders are assigned to Superadmin. Superadmin can still see and manage everything.

---

## ✅ Verification Checklist

After logging in as each user type, verify:

- [ ] Commercial can see all 96 clients in dropdown
- [ ] Commercial can see all fournisseurs in dropdown
- [ ] Commercial sees 0 orders initially (or only their own)
- [ ] Assistant can see same clients as Commercial
- [ ] Assistant cannot see Commercial's orders
- [ ] Superadmin sees all 39 orders from all users
- [ ] Dashboard shows correct scoped statistics per user
- [ ] New clients created are visible to all users
- [ ] New orders created are only visible to creator (+ superadmin)

---

**Last Updated**: 2026-02-07
**Status**: ✅ Working Correctly
