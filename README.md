# GreenShift Rijschool

Webapplicatie voor een (fictieve) rijschool. Leerlingen kopen lespakketten en plannen zelf rijlessen in, rijinstructeurs beheren hun beschikbaarheid en bekijken hun dagplanning op een kaart, en de beheerder beheert lespakketten en instructeurs.

Dit project is gemaakt als teamproject tijdens mijn opleiding Software Developer (Rijn IJssel).

## Screenshots

> _Plaats hier screenshots, bijvoorbeeld in een map `docs/screenshots/`:_
>
> `![Inloggen](docs/screenshots/login.png)`
> `![Rijles inplannen](docs/screenshots/rijles-inplannen.png)`
> `![Dagplanning instructeur](docs/screenshots/dagplanning.png)`

## Gebruikte technieken

- **PHP** met **PDO** en prepared statements
- **MySQL / MariaDB**
- **HTML en CSS**, **JavaScript**
- **Google Maps JavaScript API** voor de kaart in de dagplanning van de instructeur

## Functies

Het systeem heeft drie rollen:

**Leerling**
- Registreren met adresgegevens en een eigen rijinstructeur kiezen
- Lespakketten kopen; het resterende lestegoed staat altijd in de navigatiebalk
- Rijlessen inplannen op basis van de beschikbaarheid van de instructeur, en lessen annuleren
- Lesgeschiedenis en een overzicht van gekochte pakketten (factuur) bekijken
- Melding bij het inloggen als de rijinstructeur is gewijzigd

**Rijinstructeur**
- Weekrooster: beschikbaarheid per dag en tijdslot instellen
- Dagplanning met leerlingen, adressen en telefoonnummers
- Kaart met alle lesadressen van de dag; een adres opent direct in Google Maps

**Beheerder (admin)**
- Lespakketten toevoegen, aanpassen en deactiveren
- Rijinstructeurs toevoegen, aanpassen en verwijderen (hun leerlingen worden dan aan een andere instructeur gekoppeld)

**Beveiliging**
- Wachtwoorden gehasht met `password_hash()`
- Prepared statements tegen SQL-injectie
- Rolgebaseerde navigatie en sessiebeheer

## Lokaal installeren (XAMPP)

### Vereisten
- [XAMPP](https://www.apachefriends.org/) met PHP 8 of hoger

### Stappen

1. **Clone het project** in de `htdocs`-map van XAMPP:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/thenahomdev/greenshift.git
   ```

2. **Maak de database aan.** Start Apache en MySQL in het XAMPP Control Panel, open [phpMyAdmin](http://localhost/phpmyadmin) en importeer `database/greenshift.sql`.
   Dit maakt de database `greenshift` aan met alle tabellen, de rollen en een aantal lespakketten. Er staan geen gebruikers in.
   > Let op: bestaat de database `greenshift` al, dan worden de tabellen daarin overschreven.

3. **Stel de databaseverbinding in.** Kopieer het voorbeeldbestand en vul je gegevens in (bij een standaard XAMPP-installatie: gebruiker `root`, leeg wachtwoord):
   ```bash
   copy config\connection.example.php config\connection.php
   ```

4. **(Optioneel) Google Maps:** kopieer `config/keys.example.php` naar `config/keys.php` en vul een eigen [Google Maps API-key](https://developers.google.com/maps/documentation/javascript/get-api-key) in. Zonder key werkt alles behalve de kaart.

5. Open **http://localhost/greenshift** in je browser.

### Eerste beheerder aanmaken

Een beheerder kun je niet via de website registreren. Zo maak je er een aan:

1. Maak een wachtwoord-hash:
   ```bash
   C:\xampp\php\php.exe -r "echo password_hash('KiesEenWachtwoord', PASSWORD_DEFAULT);"
   ```
2. Voer in phpMyAdmin (database `greenshift`, tabblad SQL) uit:
   ```sql
   INSERT INTO login (roleid, email, wachtwoord, status) VALUES (1, 'admin@greenshift.test', '<hash uit stap 1>', 1);
   INSERT INTO admin (voornaam, achternaam, loginid) VALUES ('Admin', 'GreenShift', LAST_INSERT_ID());
   ```
3. Log in met `admin@greenshift.test` en voeg via **Rijinstructeuren Beheer** een instructeur toe. Daarna kunnen leerlingen zich registreren.

## Projectstructuur

```
greenshift/
├── index.php        # Laadt de navigatie en de gevraagde pagina (?page=...)
├── includes/        # Pagina's (views)
├── php/             # Verwerking van formulieren en acties
├── config/          # Databaseverbinding en API-sleutels (alleen voorbeeldbestanden in git)
├── database/        # Databasestructuur (greenshift.sql)
├── CSS/             # Opmaak
└── foto/            # Logo
```
