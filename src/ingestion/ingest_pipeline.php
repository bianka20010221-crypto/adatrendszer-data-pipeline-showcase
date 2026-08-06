<?php
/**
 * ingest_pipeline.php
 *
 * Illusztrálja a napi automatizált ingesztiós logikát: több forrás lekérése,
 * normalizálás egy közös sémára, majd QA-lépés a betöltés előtt.
 *
 * A valós verzió négy forrást köt össze (Google Analytics 4, egy webshop API,
 * egy hírlevélrendszer API, egy hűségprogram API). Ez a fájl a mintát mutatja
 * be, cég-specifikus adatok és élő végpontok nélkül.
 */

interface DataSource
{
    public function fetch(): array;
    public function name(): string;
}

final class Ga4Source implements DataSource
{
    public function __construct(private readonly string $accessToken, private readonly string $propertyId) {}

    public function fetch(): array
    {
        // GA4 Data API hívás a build_jwt()-vel szerzett access tokennel.
        // Lásd: src/auth/jwt_rs256_auth.php
        return []; // placeholder
    }

    public function name(): string { return 'ga4'; }
}

final class WebshopSource implements DataSource
{
    public function fetch(): array { return []; } // placeholder
    public function name(): string { return 'webshop'; }
}

final class NewsletterSource implements DataSource
{
    public function fetch(): array { return []; } // placeholder
    public function name(): string { return 'newsletter'; }
}

final class LoyaltySource implements DataSource
{
    public function fetch(): array { return []; } // placeholder
    public function name(): string { return 'loyalty'; }
}

/**
 * QA-lépés: minden forrásból érkező adatot ellenőriz betöltés előtt.
 * A valós rendszerben ide tartozik pl. a duplikáció-szűrés és a
 * hónapátfordulási dátumhibák kiszűrése.
 */
final class ConsistencyValidator
{
    /** @return string[] hibaüzenetek listája, üres = minden rendben */
    public function validate(string $sourceName, array $rows): array
    {
        $errors = [];

        if (empty($rows)) {
            $errors[] = "[$sourceName] üres válasz — lehetséges API-hiba, futás felfüggesztve";
        }

        // ... további konzisztencia-ellenőrzések ide kerülnek

        return $errors;
    }
}

final class IngestPipeline
{
    /** @param DataSource[] $sources */
    public function __construct(
        private readonly array $sources,
        private readonly ConsistencyValidator $validator,
        private readonly PDO $db,
    ) {}

    public function run(): void
    {
        foreach ($this->sources as $source) {
            $rows = $source->fetch();
            $errors = $this->validator->validate($source->name(), $rows);

            if ($errors !== []) {
                $this->logErrors($source->name(), $errors);
                continue; // ezt a forrást kihagyja, a többi fut tovább
            }

            $this->upsert($source->name(), $rows);
        }
    }

    private function upsert(string $sourceName, array $rows): void
    {
        // batch upsert a közös adattárba (séma-specifikus logika kihagyva)
    }

    private function logErrors(string $sourceName, array $errors): void
    {
        foreach ($errors as $e) {
            error_log("[ingest][$sourceName] $e");
        }
    }
}

// --- Ütemezett belépési pont (cron: minden nap 06:00) ---
//
// $pipeline = new IngestPipeline(
//     sources: [new Ga4Source(...), new WebshopSource(), new NewsletterSource(), new LoyaltySource()],
//     validator: new ConsistencyValidator(),
//     db: new PDO(getenv('DB_DSN'), getenv('DB_USER'), getenv('DB_PASS')),
// );
// $pipeline->run();
