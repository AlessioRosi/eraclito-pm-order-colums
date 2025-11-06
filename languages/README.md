# Traduzioni / Translations

Questa cartella contiene i file di traduzione per il plugin "Colonna Metodo di Pagamento Ordini".

## File Presenti

- `payment-method-order-column.pot` - Template di traduzione (POT)
- `payment-method-order-column-it_IT.po` - Traduzione italiana (PO)
- `payment-method-order-column-it_IT.mo` - Traduzione italiana compilata (MO)
- `compile-mo.php` - Script per compilare i file .po in .mo

## Lingua Predefinita

L'italiano (it_IT) è la lingua predefinita del plugin. Tutte le stringhe nel codice sono già in italiano.

## Come Aggiungere Nuove Traduzioni

### 1. Creare un nuovo file .po

Copia il file `payment-method-order-column.pot` e rinominalo secondo la locale desiderata:
- Inglese: `payment-method-order-column-en_US.po`
- Francese: `payment-method-order-column-fr_FR.po`
- Tedesco: `payment-method-order-column-de_DE.po`
- Spagnolo: `payment-method-order-column-es_ES.po`

### 2. Tradurre le stringhe

Apri il file .po con un editor di testo o uno strumento come Poedit e traduci le stringhe:

```
msgid "Metodo di Pagamento"
msgstr "Payment Method"
```

### 3. Compilare il file .mo

Esegui lo script di compilazione:

```bash
php compile-mo.php
```

Oppure usa Poedit o msgfmt:

```bash
msgfmt payment-method-order-column-en_US.po -o payment-method-order-column-en_US.mo
```

## Testare le Traduzioni

1. Carica i file .po e .mo nella cartella `/languages/`
2. Vai in WordPress > Impostazioni > Generali
3. Cambia la lingua del sito
4. Le stringhe del plugin verranno tradotte automaticamente

## Stringhe Disponibili per la Traduzione

Il plugin include le seguenti stringhe traducibili:

- Titoli menu e pagine
- Etichette colonne
- Messaggi di errore e successo
- Testi dei bottoni
- Tooltip e descrizioni

## Note per gli Sviluppatori

Se aggiungi nuove stringhe traducibili al codice:

1. Usa sempre `__()` o `esc_html__()` con il text domain `payment-method-order-column`
2. Rigenera il file .pot
3. Aggiorna tutti i file .po esistenti
4. Ricompila i file .mo

## Contribuire

Per contribuire con nuove traduzioni, apri una pull request o contatta:
- Email: info@eraclito.it
- Sito: https://www.eraclito.it
