<?php

declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Data\Configuration;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
abstract class AbstractPandaDataConfiguration implements DataConfigurationInterface
{
    protected array $errors = [];
    protected ?string $currentField = null;
    protected ?string $field_display_name = null;

    public const ERROR_INVALID        = 'invalid';
    public const ERROR_EMPTY          = 'empty';
    public const ERROR_TOO_LONG       = 'too_long';
    public const ERROR_TOO_SHORT      = 'too_short';
    public const ERROR_PATTERN        = 'pattern';
    public const ERROR_NOT_FOUND      = 'not_found';
    public const ERROR_DUPLICATE      = 'duplicate';
    public const ERROR_FORBIDDEN      = 'forbidden';
    public const ERROR_SAVE_FAILED    = 'save_failed';
    public const ERROR_INVALID_EMAIL  = 'invalid_email';
    public const ERROR_INVALID_URL    = 'invalid_url';
    public const ERROR_INVALID_DATE   = 'invalid_date';
    public const ERROR_OUT_OF_RANGE   = 'out_of_range';
    public const ERROR_PERMISSION     = 'permission_denied';

    protected array $errorMessages = [
        self::ERROR_INVALID        => 'Nieprawidłowa wartość.',
        self::ERROR_EMPTY          => 'To pole nie może być puste.',
        self::ERROR_TOO_LONG       => 'Wartość jest za długa (maksymalnie %d znaków).',
        self::ERROR_TOO_SHORT      => 'Wartość jest za krótka (minimalnie %d znaków).',
        self::ERROR_PATTERN        => 'Niepoprawny format danych.',
        self::ERROR_NOT_FOUND      => 'Zasób %s o identyfikatorze %d nie istnieje.',
        self::ERROR_DUPLICATE      => 'Wartość już istnieje w bazie danych.',
        self::ERROR_FORBIDDEN      => 'Użycie niedozwolonych znaków.',
        self::ERROR_SAVE_FAILED    => 'Wystąpił błąd podczas zapisu danych.',
        self::ERROR_INVALID_EMAIL  => 'Nieprawidłowy adres e-mail.',
        self::ERROR_INVALID_URL    => 'Nieprawidłowy adres URL.',
        self::ERROR_INVALID_DATE   => 'Nieprawidłowa data.',
        self::ERROR_OUT_OF_RANGE   => 'Wartość poza dozwolonym zakresem (%d - %d).',
        self::ERROR_PERMISSION     => 'Brak uprawnień do wykonania tej operacji.',
    ];

    protected function setField(string $field,?string $field_display_name = null): void
    {
        $this->currentField = $field;
        $this->field_display_name = $field_display_name;
    }

    protected function addError(?string $type = null, array $context = [], ?string $customMessage = null): void
    {
        $field = $this->currentField ?? '_global';

        if ($type === null) {
            $message = $customMessage ?? 'Wystąpił błąd.';
        } else {
            if (isset($this->errorMessages[$type])) {
                // Używamy vsprintf tylko dla dodatkowego kontekstu (np. liczby znaków)
                $message = @vsprintf($this->errorMessages[$type], $context);
                if ($message === false) {
                    $message = $this->errorMessages[$type];
                }
            } else {
                $message = $customMessage ?? 'Nieznany błąd.';
            }
        }
        if($field !== '_global' && $this->field_display_name != null){
            $message = "Pole {$this->field_display_name}: $message";
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    protected function clearErrors(){
        $this->errors = [];
    }
    protected function clearField(){
        $this->currentField = null;
        $this->field_display_name = null;
    }

}