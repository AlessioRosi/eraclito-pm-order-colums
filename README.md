# Colonna Metodo di Pagamento Ordini

Plugin WordPress per WooCommerce che aggiunge una colonna personalizzabile per visualizzare e filtrare gli ordini in base al metodo di pagamento.

## Caratteristiche

✅ **Colonna Metodo di Pagamento** nella lista ordini di WooCommerce
✅ **Filtro dropdown** per filtrare gli ordini per metodo di pagamento
✅ **Colonna ordinabile** per ordinare gli ordini per metodo di pagamento
✅ **Icone personalizzabili** per ogni metodo di pagamento
✅ **Compatibilità HPOS** (High-Performance Order Storage)
✅ **Supporto CPT** (Custom Post Types) legacy
✅ **Interfaccia completamente in italiano**
✅ **Sistema di traduzioni** per altre lingue

## Requisiti

- WordPress 5.0 o superiore
- WooCommerce 4.0 o superiore
- PHP 7.2 o superiore
- WooCommerce testato fino alla versione 10.3.4

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

Il plugin include icone predefinite per:

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
│   ├── compile-mo.php
│   └── README.md
├── era-pmoc.php
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
- ✅ Filtri ottimizzati con `meta_query`
- ✅ Funziona con entrambi i sistemi (HPOS e CPT)

## Traduzioni

Il plugin è disponibile in:

- 🇮🇹 **Italiano** (predefinito)
- 🌍 Altre lingue tramite file .po/.mo

Per aggiungere nuove traduzioni, consulta il file `languages/README.md`.

## Supporto

Per supporto e segnalazioni:

- **Email**: info@eraclito.it
- **Sito**: https://www.eraclito.it
- **Plugin URI**: https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/

## Changelog

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
