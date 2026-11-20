<?php

namespace InEngine\ModulusComposer\Composer;

use InEngine\Modulus\Upgrades\UpgradeConstraintPolicy;
use InEngine\ModulusComposer\Exceptions\InvalidComposerJsonException;

/**
 * Rewrites host composer.json require constraints (SemVer policy).
 */
final class RootConstraintRewriter
{
    public function __construct(
        protected string $composerJsonPath = '',
        protected UpgradeConstraintPolicy $policy = new UpgradeConstraintPolicy,
    ) {
        if ($this->composerJsonPath === '') {
            $this->composerJsonPath = base_path('composer.json');
        }
    }

    /**
     * Build a rewriter for the configured working directory's composer.json.
     *
     * @param  UpgradeConstraintPolicy|null  $policy  Defaults to `modulus.upgrades.default_constraint`
     */
    public static function fromConfig(?UpgradeConstraintPolicy $policy = null): self
    {
        $cwd = config('modulus-composer.working_directory', '');
        $cwd = is_string($cwd) && $cwd !== '' ? $cwd : base_path();

        return new self(
            composerJsonPath: $cwd.DIRECTORY_SEPARATOR.'composer.json',
            policy: $policy ?? UpgradeConstraintPolicy::fromConfig(
                config('modulus.upgrades.default_constraint', 'patch')
            ),
        );
    }

    /**
     * Current `require` constraint for the package.
     *
     * @return string|null Null when the package is not required
     */
    public function get(string $package): ?string
    {
        $require = $this->read()['require'] ?? [];

        if (! is_array($require)) {
            return null;
        }

        $constraint = $require[$package] ?? null;

        return is_string($constraint) ? $constraint : null;
    }

    /**
     * Set an explicit constraint string for the package.
     */
    public function set(string $package, string $constraint): void
    {
        $data = $this->read();
        $require = $data['require'] ?? [];

        if (! is_array($require)) {
            $require = [];
        }

        $require[$package] = $constraint;
        ksort($require);
        $data['require'] = $require;

        $this->write($data);
    }

    /**
     * Set constraint using the host upgrade policy for a concrete version.
     */
    public function setSuggested(string $package, string $version): string
    {
        $constraint = $this->policy->suggestConstraint($version);
        $this->set($package, $constraint);

        return $constraint;
    }

    /**
     * @return array<string, mixed>
     */
    protected function read(): array
    {
        if (! is_file($this->composerJsonPath)) {
            throw new InvalidComposerJsonException("Missing composer.json at [{$this->composerJsonPath}].");
        }

        $raw = file_get_contents($this->composerJsonPath);

        if ($raw === false || $raw === '') {
            throw new InvalidComposerJsonException("Unable to read composer.json at [{$this->composerJsonPath}].");
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidComposerJsonException(
                "Invalid JSON in composer.json at [{$this->composerJsonPath}]: ".$e->getMessage(),
                previous: $e,
            );
        }

        if (! is_array($decoded)) {
            throw new InvalidComposerJsonException("composer.json must decode to an object at [{$this->composerJsonPath}].");
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function write(array $data): void
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        )."\n";

        if (file_put_contents($this->composerJsonPath, $json) === false) {
            throw new InvalidComposerJsonException("Unable to write composer.json at [{$this->composerJsonPath}].");
        }
    }
}
