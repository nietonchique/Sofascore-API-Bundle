<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Dto;

/**
 * Known SofaScore translation locale codes.
 *
 * The API may return additional codes over time; this is a convenience
 * catalogue, not a closed enum, so consumers are not blocked from passing
 * arbitrary strings to {@see FieldTranslations::nameIn()}.
 */
final class LanguageCode
{
    public const EN = 'en';
    public const RU = 'ru';
    public const SR = 'sr';
    public const AR = 'ar';
    public const HI = 'hi';
    public const BN = 'bn';
    public const HR = 'hr';
    public const DE = 'de';
    public const ES = 'es';
    public const FR = 'fr';
    public const IT = 'it';
    public const PT = 'pt';
    public const NL = 'nl';
    public const TR = 'tr';
    public const PL = 'pl';
    public const UK = 'uk';
    public const ZH = 'zh';
    public const JA = 'ja';
    public const KO = 'ko';
    public const CS = 'cs';
    public const SK = 'sk';
    public const EL = 'el';
    public const HE = 'he';
    public const ID = 'id';
    public const TH = 'th';
    public const VI = 'vi';
    public const SV = 'sv';
    public const DA = 'da';
    public const FI = 'fi';
    public const NO = 'no';
    public const RO = 'ro';
    public const HU = 'hu';
    public const BG = 'bg';
    public const AZ = 'az';
    public const KA = 'ka';
    public const BE = 'be';
    public const KK = 'kk';
    public const UZ = 'uz';

    /**
     * @var array<string, string>
     */
    public const LABELS = [
        self::EN => 'English',
        self::RU => 'Russian',
        self::SR => 'Serbian',
        self::AR => 'Arabic',
        self::HI => 'Hindi',
        self::BN => 'Bengali',
        self::HR => 'Croatian',
        self::DE => 'German',
        self::ES => 'Spanish',
        self::FR => 'French',
        self::IT => 'Italian',
        self::PT => 'Portuguese',
        self::NL => 'Dutch',
        self::TR => 'Turkish',
        self::PL => 'Polish',
        self::UK => 'Ukrainian',
        self::ZH => 'Chinese',
        self::JA => 'Japanese',
        self::KO => 'Korean',
        self::CS => 'Czech',
        self::SK => 'Slovak',
        self::EL => 'Greek',
        self::HE => 'Hebrew',
        self::ID => 'Indonesian',
        self::TH => 'Thai',
        self::VI => 'Vietnamese',
        self::SV => 'Swedish',
        self::DA => 'Danish',
        self::FI => 'Finnish',
        self::NO => 'Norwegian',
        self::RO => 'Romanian',
        self::HU => 'Hungarian',
        self::BG => 'Bulgarian',
        self::AZ => 'Azerbaijani',
        self::KA => 'Georgian',
        self::BE => 'Belarusian',
        self::KK => 'Kazakh',
        self::UZ => 'Uzbek',
    ];

    public static function label(string $code): string
    {
        return self::LABELS[$code] ?? $code;
    }
}
