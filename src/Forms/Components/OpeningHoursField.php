<?php

namespace Fayne\OpeningHours\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class OpeningHoursField extends Field
{
    protected string $view = 'filament-opening-hours::opening-hours';

    protected array $hoursOptions = [];

    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function hoursOptions(array $options): static
    {
        $this->hoursOptions = $options;
        $this->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
            try {
                $this->validateHours($value);
            } catch (\InvalidArgumentException $exception) {
                $fail($exception->getMessage());
            }
        });

        return $this;
    }

    public function hydrateState(?array &$hydratedDefaultState, bool $shouldCallHydrationHooks = true, bool $shouldApplyStateCasts = true, array &$appliedStateCastPaths = []): void
    {
        $value = $this->getRawState();
        if (is_string($value)) {
            $this->rawState($value === '' ? [] : json_decode($value, true, flags: JSON_THROW_ON_ERROR));
        }
        parent::hydrateState($hydratedDefaultState, $shouldCallHydrationHooks, $shouldApplyStateCasts, $appliedStateCastPaths);
    }

    public function usesTextInputs(): bool
    {
        return $this->hoursOptions['useTextInputs'] ?? false;
    }

    public function getDays(): array
    {
        return __('filament-opening-hours::opening-hours.days');
    }

    public function allowsExceptions(): bool
    {
        return $this->hoursOptions['allowExceptions'] ?? true;
    }

    public function validateHours(mixed $value): void
    {
        if ($value === null) {
            return;
        }
        if (! is_array($value)) {
            throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.week_map'));
        }
        foreach (self::DAYS as $day) {
            $this->validateRanges($value[$day] ?? []);
        }
        $exceptions = $value['exceptions'] ?? [];
        if (! is_array($exceptions) || (! $this->allowsExceptions() && $exceptions !== [])) {
            throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.exceptions_not_allowed'));
        }
        foreach ($exceptions as $date => $ranges) {
            if (! is_string($date) || ! preg_match('/^(?:(\d{4})-)?(\d{2})-(\d{2})$/', $date, $match) || ! checkdate((int) $match[2], (int) $match[3], (int) ($match[1] ?: 2000))) {
                throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.exception_dates'));
            }
            $this->validateRanges($ranges);
        }
    }

    private function validateRanges(mixed $ranges): void
    {
        if (is_array($ranges) && array_key_exists('hours', $ranges)) {
            $ranges = $ranges['hours'];
        }
        if (! is_array($ranges) || ! array_is_list($ranges)) {
            throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.ranges_list'));
        }
        $seen = [];
        foreach ($ranges as $range) {
            if (! is_string($range) || ! preg_match('/^([0-2]\d):([0-5]\d)-([0-2]\d):([0-5]\d)$/', $range, $match)) {
                throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.range_format'));
            }
            $start = (int) $match[1] * 60 + (int) $match[2];
            $end = (int) $match[3] * 60 + (int) $match[4];
            if ($start >= 1440 || $end > 1440 || $start === $end || ($end < $start && ! ($this->hoursOptions['allowOverflowMidnight'] ?? false))) {
                throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.range_invalid'));
            }
            if ($end < $start) {
                $end += 1440;
            }
            foreach ($seen as [$previousStart, $previousEnd]) {
                if ($start < $previousEnd && $end > $previousStart) {
                    throw new \InvalidArgumentException(__('filament-opening-hours::opening-hours.range_overlap'));
                }
            }
            $seen[] = [$start, $end];
        }
    }
}
