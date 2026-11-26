# Colonna Metodo di Pagamento Ordini

Plugin WordPress per WooCommerce che aggiunge una colonna personalizzabile per visualizzare e filtrare gli ordini in base al metodo di pagamento.

## Caratteristiche

✅ **Colonna Metodo di Pagamento** nella lista ordini di WooCommerce
✅ **Filtro dropdown** per filtrare gli ordini per metodo di pagamento
✅ **Colonna ordinabile** per ordinare gli ordini per metodo di pagamento
✅ **Icone personalizzabili** per ogni metodo di pagamento
✅ **Compatibilità HPOS** (High-Performance Order Storage)
✅ **Supporto CPT** (Custom Post Types) legacy
✅ **Interfaccia in inglese** con traduzione italiana inclusa, estendibile ad altre lingue

## Requisiti

- WordPress 5.0 o superiore
- WooCommerce 4.0 o superiore
- PHP 7.4 o superiore
- Testato fino a WordPress 7.1.5 e WooCommerce 11.3.1

## Installazione

1. Carica la cartella `eraclito-pm-order-column` nella directory `/wp-content/plugins/`
2. Attiva il plugin tramite il menu 'Plugin' in WordPress
3. Vai su WooCommerce → Colonna Pagamento per configurare le icone

## Utilizzo

### Visualizzazione Colonna

Dopo l'attivazione, nella lista ordini di WooCommerce apparirà automaticamente una nuova colonna "Metodo di Pagamento" con l'icona corrispondente.

### Filtraggio Ordini

Sopra la lista ordini apparirà un dropdown "Filtra per metodo di pagamento" che permette di filtrare gli ordini per metodo di pagamento specifico.

### Ordinamento

Clicca sull'intestazione della colonna "Metodo di Pagamento" per ordinare gli ordini per metodo di pagamento.

### Personalizzazione Icone

1. Vai su **WooCommerce → Colonna Pagamento**
2. Per ogni metodo di pagamento puoi:
   - **Caricare un'icona personalizzata** usando il bottone "Carica Icona"
   - **Ripristinare l'icona predefinita** usando il bottone "Ripristina Predefinita"
3. Clicca su "Salva Impostazioni" per applicare le modifiche

## Metodi di Pagamento Supportati

Il plugin include icone predefinite per i metodi seguenti. Il menu filtro e la pagina impostazioni elencano tutti i gateway registrati, anche quelli disattivati, così gli ordini vecchi restano filtrabili:

- 💵 Contrassegno (COD)
- 🏦 Bonifico Bancario (BACS)
- 📄 Assegno (Cheque)
- 💳 PayPal
- 💳 Stripe
- 📋 Generico (per altri metodi)

## Struttura File

```
eraclito-pm-order-column/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── img/
│   ├── cash.svg
│   ├── wired-transfer.svg
│   ├── bollettino.svg
│   ├── credit-card.svg
│   ├── stripe.svg
│   └── generic.svg
├── languages/
│   ├── payment-method-order-column.pot
│   ├── payment-method-order-column-it_IT.po
│   ├── payment-method-order-column-it_IT.mo
│   ├── payment-method-order-column-en_US.po
│   ├── payment-method-order-column-en_US.mo
│   ├── compile-mo.php
│   └── README.md
├── era-pmoc.php
├── readme.txt
└── README.md
```

## Best Practice Implementate

- ✅ **Singleton Pattern** per la classe principale
- ✅ **Hooks separation** per CPT e HPOS
- ✅ **Security first** con sanitizzazione e escaping completo
- ✅ **WordPress Coding Standards** rispettati
- ✅ **Object-Oriented Programming**
- ✅ **Nessuna dipendenza esterna**
- ✅ **Performance ottimizzate**

## Compatibilità HPOS

Il plugin è completamente compatibile con il nuovo sistema HPOS di WooCommerce:

- ✅ Dichiarazione formale di compatibilità
- ✅ Supporto per `woocommerce_page_wc-orders` screen
- ✅ Hooks specifici per HPOS
- ✅ Filtro tramite l'argomento nativo `payment_method` (colonna di `wc_orders`)
- ✅ Ordinamento tramite `woocommerce_orders_table_query_clauses`
- ✅ Funziona con entrambi i sistemi (HPOS e CPT)

## Traduzioni

Il plugin è disponibile in:

- 🇺🇸 **Inglese** (lingua del codice)
- 🇮🇹 **Italiano** (it_IT)
- 🌍 Altre lingue tramite file .po/.mo

La traduzione italiana si attiva impostando la lingua del sito su Italiano in Impostazioni → Generali.

Per aggiungere nuove traduzioni, consulta il file `languages/README.md`.

## Supporto

Per supporto e segnalazioni:

- **Email**: info@eraclito.it
- **Sito**: https://www.eraclito.it
- **Plugin URI**: https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/

## Changelog

### 3.1.1 (2026-11-26)
- 🔖 Bump di versione

### 3.1.0 (2026-11-26)
- ✨ Nuovo layout della pagina impostazioni (banner, pannello, brand Eraclito)
- ✨ Stringhe dell'interfaccia in inglese, traduzione italiana inclusa
- ✅ Verificata compatibilità con WordPress 7.1.5 e WooCommerce 11.3.1
- ✨ Aggiunto `uninstall.php` che rimuove le impostazioni
- ✨ Aggiunte icone, banner e screenshot per la directory dei plugin

### 3.0.4 (2026-10-30)
- ✅ Verificata compatibilità con WordPress 7.1.4 e WooCommerce 11.2.1
- 🐛 Fix ordinamento per metodo di pagamento (HPOS e legacy)
- 🐛 Fix filtro per metodo di pagamento con HPOS (colonna nativa invece di meta)
- 🐛 Menu filtro e impostazioni elencano anche i gateway disattivati
- 🐛 Fix file di lingua: header .mo corretto, msgid duplicato rimosso
- ✨ Aggiunta traduzione en_US
- 🔧 Requisito minimo PHP 7.4

### 3.0.0 (2025-01-06)
- ✨ Aggiunto supporto HPOS completo
- ✨ Sistema di traduzioni implementato
- ✨ Interfaccia completamente in italiano
- ✨ Pagina impostazioni per personalizzazione icone
- ✨ Upload icone custom via Media Library
- ✨ Notice modifiche non salvate
- ✨ Preview icone in tempo reale
- 🔧 Refactoring completo con OOP
- 🔧 Best practice WordPress implementate
- 🔧 Struttura file riorganizzata

### 2.0.0
- ✨ Refactoring con classe Singleton
- ✨ Miglioramenti sicurezza
- ✨ Codice ottimizzato

### 1.6.5
- 🐛 Fix minori
- 📝 Miglioramento documentazione

## Licenza

GPL2 - https://www.gnu.org/licenses/gpl-2.0.html

## Credits

Sviluppato da **Eraclito - Alessio Rosi**
© 2025 Eraclito. Tutti i diritti riservati.
