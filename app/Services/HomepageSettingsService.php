<?php

namespace App\Services;

use App\Models\Application;
use App\Models\SystemSetting;
class HomepageSettingsService
{
    public const KEY = 'homepage';

    public const MODE_DEFAULT = 'default';

    public const MODE_APPLICATION = 'application';

    /**
     * @return array{mode: string, application_id: int|null, application?: array{id: int, name: string, slug: string}|null}
     */
    public function get(): array
    {
        $record = SystemSetting::query()->where('key', self::KEY)->first();
        $payload = is_array($record?->payload) ? $record->payload : [];

        $mode = ($payload['mode'] ?? self::MODE_APPLICATION) === self::MODE_DEFAULT
            ? self::MODE_DEFAULT
            : self::MODE_APPLICATION;

        $applicationId = isset($payload['application_id']) ? (int) $payload['application_id'] : null;
        $application = $this->resolveApplication($applicationId);

        if ($mode === self::MODE_APPLICATION && $application) {
            $applicationId = $application->id;
        }

        return [
            'mode' => $mode,
            'application_id' => $applicationId,
            'application' => $application ? [
                'id' => $application->id,
                'name' => $application->name,
                'slug' => $application->slug,
            ] : null,
        ];
    }

    /**
     * @param  array{mode: string, application_id?: int|null}  $input
     * @return array{mode: string, application_id: int|null, application?: array{id: int, name: string, slug: string}|null}
     */
    public function update(array $input): array
    {
        $mode = ($input['mode'] ?? self::MODE_APPLICATION) === self::MODE_DEFAULT
            ? self::MODE_DEFAULT
            : self::MODE_APPLICATION;

        $applicationId = isset($input['application_id']) ? (int) $input['application_id'] : null;

        if ($mode === self::MODE_APPLICATION) {
            $application = $this->resolveApplication($applicationId);
            if (! $application) {
                abort(422, 'Pilih aplikasi untuk landing homepage.');
            }
            $applicationId = $application->id;
        } else {
            $applicationId = null;
        }

        SystemSetting::query()->updateOrCreate(
            ['key' => self::KEY],
            ['payload' => [
                'mode' => $mode,
                'application_id' => $applicationId,
            ]],
        );

        return $this->get();
    }

    public function homepageApplication(): ?Application
    {
        $settings = $this->get();
        if ($settings['mode'] === self::MODE_DEFAULT) {
            return null;
        }

        return $this->resolveApplication($settings['application_id']);
    }

    private function resolveApplication(?int $applicationId): ?Application
    {
        if ($applicationId) {
            $app = Application::query()
                ->where('id', $applicationId)
                ->where('is_active', true)
                ->first();
            if ($app) {
                return $app;
            }
        }

        $slug = (string) config('softsale.default_application_slug', 'spot-color');

        $bySlug = Application::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($bySlug) {
            return $bySlug;
        }

        return Application::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }
}
