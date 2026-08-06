[README.md](https://github.com/user-attachments/files/30787154/README.md)
# Adatintegrációs & automatizációs workflow — portfólió-kivonat

> **Megjegyzés:** ez egy éles, üzemben futó vállalati adatrendszer **sanitizált,
> illusztratív kivonata**. A valós kódból eltávolítottam minden hitelesítő
> adatot, hosztnevet, adatbázis-sémanevet és cég-specifikus üzleti logikát.
> A cél nem a teljes forráskód közzététele, hanem az architektúra és a
> kódolási minőség bemutatása.

## Mit csinál a rendszer?

Négy külső, egymástól független API-t (webanalitika, webshop, hírlevélrendszer,
hűségprogram) hangol össze egy közös adattárba, napi ütemezéssel, beépített
minőségbiztosítási (QA) lépéssel, mielőtt az adat vezetői riportokba kerül.

![Workflow diagram](diagrams/emmarozs_workflow.png)

## Miért épült így?

- **Egy forrás se blokkolja a többit.** Ha az egyik API hibázik vagy üres
  választ ad, a pipeline naplózza a hibát és folytatja a többi forrással —
  nem áll le az egész éjszakai futás egyetlen API-hiba miatt.
- **QA a betöltés előtt, nem után.** A konzisztencia-ellenőrzés (duplikációk,
  dátumhibák) a betöltési lépés *része*, nem utólagos javítgatás.
- **Nincs külső JWT-könyvtár.** A Google service-account autentikáció
  (JWT header + claims összeállítása, RS256 aláírás, OAuth2 token-csere)
  nulláról, `openssl_sign()`-nal van implementálva — ez mélyebb kontrollt ad
  a tokenéletciklus felett, mint egy kész SDK.

## Fájlstruktúra

```
├── README.md
├── .env.example              ← placeholder konfiguráció (SOHA nincs benne valós titok)
├── diagrams/
│   └── emmarozs_workflow.png ← a fenti architektúra-ábra
└── src/
    ├── auth/
    │   └── jwt_rs256_auth.php     ← egyedi JWT/RS256 autentikáció
    └── ingestion/
        └── ingest_pipeline.php    ← többforrású ingesztiós minta + QA-lépés
```

## Tech stack

PHP · MySQL · REST API-k · cron-ütemezés · JWT/RS256 (saját implementáció) ·
Notion API (riport-szinkronhoz)

## Kapcsolódó

A teljes workflow-portfólió (ez a rendszer + egy másik, rendelésfeldolgozási
automatizáció) egy külön PDF-ben is elérhető, illetve szívesen bemutatom
élőben egy beszélgetés keretében.
