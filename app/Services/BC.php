<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use InvalidArgumentException;
use UnexpectedValueException;

class BC
{

    public const COMPARE_EQUAL = 0;
    public const COMPARE_LEFT_GRATER = 1;
    public const COMPARE_RIGHT_GRATER = -1;

    protected const DEFAULT_SCALE = 100;

    protected const MAX_BASE = 256;

    protected const BIT_OPERATOR_AND = 'and';
    protected const BIT_OPERATOR_OR = 'or';
    protected const BIT_OPERATOR_XOR = 'xor';

    /**
     * @var bool
     */
    protected static bool $trimTrailingZeroes = true;

    /**
     * @param ...$numbers
     * @return string
     */
    public static function sum(...$numbers): string
    {
        return array_reduce(func_get_args(), static fn ($a, $c) => BC::add($a, $c, 8), '0');
    }

    /**
     * @param $total
     * @param $ownValue
     * @param null|int $scale
     * @return int|string
     */
    public static function getPercentageFromTotal($total, $ownValue, ?int $scale = null)
    {
        [$total, $ownValue] = [self::convertScientificNotationToString($total), self::convertScientificNotationToString($ownValue)];

        if (! self::isGreaterThanZero($total)) {
            return 0;
        }

        $scale = $scale ?? max([strlen($total), strlen($ownValue)]);

        return self::mul(self::div($ownValue, $total, 18), '100', $scale);
    }

    /**
     * @param float $num
     * @param null|int $scale
     * @return int|string
     */
    public static function realNum(float $num, ?int $scale = null)
    {
        if (!is_numeric($num) || is_nan($num)) {
            return 0;
        }

        $scale = $scale ?? max(strlen($num));

        $r = number_format($num, $scale);

        if (false !== strpos($r, '.')) {
            $r = rtrim(rtrim($r, '0'), '.');
        }

        return $r;
    }

    /**
     * @param array $bn
     * @return string
     */
    public static function arrayBnToString(array $bn): string
    {
        [$left, $right] = $bn;

        $r = str_pad($right->toString(), 18, '0', STR_PAD_LEFT);

        return sprintf("%s.%s", $left->toString(), $r);
    }

    /**
     * @param $number
     * @return string
     */
    public static function toBn($number): string
    {
        return is_array($number) ? self::arrayBnToString($number) : self::convertScientificNotationToString((string) $number);
    }

    /**
     * @param $number
     * @param $unit
     * @return string
     */
    public static function fromWei($number, $unit): string
    {
        return self::toBn(Utils::fromWei($number, $unit));
    }

    /**
     * @param $number
     * @param $unit
     * @return string
     */
    public static function toEther($number, $unit): string
    {
        return is_string($unit) ?
            self::toBn(Utils::toEther($number, $unit)) :
            self::toBn(self::div($number, (string) $unit, 18));
    }

    /**
     * @param $number
     * @param $unit
     * @return string
     */
    public static function toWei($number, $unit): string
    {
        return self::toBn(Utils::toWei($number, $unit));
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return bool
     */
    public static function isGreaterThan(string $leftOperand, string $rightOperand, ?int $scale = null): bool
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        $scale = $scale ?? max(strlen($leftOperand), strlen($rightOperand));

        return self::comp($leftOperand, $rightOperand, $scale) === 1;
    }

    /**
     * @param string $value
     * @param null|int $scale
     * @return bool
     */
    public static function isGreaterThanZero(string $value, ?int $scale = null): bool
    {
        $value = static::convertScientificNotationToString($value);

        $scale = $scale ?? strlen($value);

        return self::isGreaterThan($value, '0', $scale);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return bool
     */
    public static function isGreaterOrEqualTo(string $leftOperand, string $rightOperand, ?int $scale = null): bool
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        $scale = $scale ?? max(strlen($leftOperand), strlen($rightOperand));

        return in_array(self::comp($leftOperand, $rightOperand, $scale), [0, 1]);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return bool
     */
    public static function isEqualTo(string $leftOperand, string $rightOperand, ?int $scale = null): bool
    {
        return self::comp($leftOperand, $rightOperand, $scale) === 0;
    }

    /**
     * @param string $value
     * @param string $percentage
     * @param int|null $scale
     * @return string
     */
    public static function substractPercentage(string $value, string $percentage, ?int $scale = null): string
    {
        $value = static::convertScientificNotationToString($value);
        $percentage = static::convertScientificNotationToString($percentage);

        $percentageValue = static::div(static::sub("100", $percentage, 8), "100", 8);

        $r = null === $scale ? static::mul($value, $percentageValue) : static::mul($value, $percentageValue, $scale);
        return static::formatTrailingZeroes($r, $scale);
    }

    /**
     * @param string $value
     * @param string $percentage
     * @param int|null $scale
     * @return string
     */
    public static function getPercentage(string $value, string $percentage, ?int $scale = null): string
    {
        $value = static::convertScientificNotationToString($value);
        $percentage = static::convertScientificNotationToString($percentage);

        $percentageValue = static::div($percentage, "100", 18);

        $r = null === $scale ? static::mul($percentageValue, $value) : static::mul($percentageValue, $value, $scale);

        return static::formatTrailingZeroes($r, $scale);
    }

    /**
     * @param string $value
     * @param string $total
     * @param null|int $scale
     * @return string
     */
    public static function getLoadedPercentage(string $value, string $total, ?int $scale = null): string
    {
        $value = static::convertScientificNotationToString($value);
        $total = static::convertScientificNotationToString($total);

        $scale = $scale ?? max(strlen($value), strlen($total));

        return self::mul(self::div($value, $total, $scale), '100', $scale);
    }

    /**
     * @param string $n1
     * @param string $n2
     * @param int|null $scale
     * @return string
     */
    public static function percentageDifference(string $n1, string $n2, ?int $scale = null): string
    {
        $n1 = static::convertScientificNotationToString($n1);
        $n2 = static::convertScientificNotationToString($n2);

        $scale = $scale ?? max(strlen($n1), strlen($n2));

        if (!self::isGreaterThanZero($n1, $scale) && !self::isGreaterThanZero($n2, $scale)) {
            return '0';
        }

        if (!self::isGreaterThanZero($n1, $scale)) {
            return $n2;
        }

        return self::mul(self::div(self::sub($n2, $n1, 8), $n1, 8), '100', $scale);
    }

    /**
     * @param string $value
     * @param string $percentage
     * @param null|int $scale
     * @return string
     */
    public static function addPercentage(string $value, string $percentage, ?int $scale = null): string
    {
        $value = static::convertScientificNotationToString($value);
        $percentage = static::convertScientificNotationToString($percentage);

        $scale = $scale ?? strlen($value);

        $percentageValue = self::getPercentage($value, $percentage, $scale);

        return self::add($value, $percentageValue, $scale);
    }

    /**
     * @param string $min
     * @param string $max
     * @return string
     */
    public static function rand(string $min, string $max): string
    {
        $max = static::convertScientificNotationToString($max);
        $min = static::convertScientificNotationToString($min);

        $difference = static::add(static::sub($max, $min), '1');
        $randPercent = static::div((string)mt_rand(), (string)mt_getrandmax(), 8);

        return static::add($min, static::mul($difference, $randPercent, 8), 0);
    }

    /**
     * @param string $number
     * @return string
     */
    public static function convertScientificNotationToString(string $number): string
    {
        // check if number is in scientific notation, first use stripos as is faster then preg_match
        if (false !== stripos($number, 'E') && preg_match('/(-?(\d+\.)?\d+)E([+-]?)(\d+)/i', $number, $regs)) {
            // calculate final scale of number
            $scale = $regs[4] + static::getDecimalsLength($regs[1]);
            $pow = static::pow('10', $regs[4], $scale);
            if ('-' === $regs[3]) {
                $number = static::div($regs[1], $pow, $scale);
            } else {
                $number = static::mul($pow, $regs[1], $scale);
            }
            $number = static::formatTrailingZeroes($number, $scale);
        }

        return static::parseNumber($number);
    }

    /**
     * @param string $number
     * @return int
     */
    public static function getDecimalsLength(string $number): int
    {
        if (static::isFloat($number)) {
            return strcspn(strrev($number), '.');
        }

        return 0;
    }

    protected static function isFloat(string $number): bool
    {
        return false !== strpos($number, '.');
    }

    /**
     * @param string $base
     * @param string $exponent
     * @param int|null $scale
     * @return string
     */
    public static function pow(string $base, string $exponent, ?int $scale = null): string
    {
        $base = static::convertScientificNotationToString($base);
        $exponent = static::convertScientificNotationToString($exponent);

        if (static::isFloat($exponent)) {
            $r = static::powFractional($base, $exponent, $scale);
        } elseif (null === $scale) {
            $r = bcpow($base, $exponent);
        } else {
            $r = bcpow($base, $exponent, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    protected static function powFractional(string $base, string $exponent, ?int $scale = null): string
    {
        // we need to increased scale to get correct results and avoid rounding error
        $currentScale = $scale ?? static::getScale();
        $increasedScale = $currentScale * 2;

        // add zero to trim scale
        return static::parseNumber(
            static::add(
                static::exp(static::mul($exponent, static::log($base), $increasedScale)),
                '0',
                $currentScale
            )
        );
    }

    /**
     * @return int
     */
    public static function getScale(): int
    {
        if (PHP_VERSION_ID >= 70300) {
            /** @noinspection PhpStrictTypeCheckingInspection */
            /** @noinspection PhpParamsInspection */
            return bcscale();
        }

        $sqrt = static::sqrt('2');

        return strlen(substr($sqrt, strpos($sqrt, '.') + 1));
    }

    /**
     * @param string $number
     * @param int|null $scale
     * @return string
     */
    public static function sqrt(string $number, ?int $scale = null): string
    {
        $number = static::convertScientificNotationToString($number);

        if (null === $scale) {
            $r = bcsqrt($number);
        } else {
            $r = bcsqrt($number, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    protected static function formatTrailingZeroes(string $number, ?int $scale = null): string
    {
        if (self::$trimTrailingZeroes) {
            return static::trimTrailingZeroes($number);
        }

        // newer version of php correct add trailing zeros
        if (PHP_VERSION_ID >= 70300) {
            return $number;
        }

        // old one not so much..
        return self::addTrailingZeroes($number, $scale);
    }

    protected static function trimTrailingZeroes(string $number): string
    {
        if (static::isFloat($number)) {
            $number = rtrim($number, '0');
        }

        return rtrim($number, '.') ?: '0';
    }

    protected static function addTrailingZeroes(string $number, ?int $scale): string
    {
        if (null === $scale) {
            return $number;
        }

        $decimalLength = static::getDecimalsLength($number);
        if ($scale === $decimalLength) {
            return $number;
        }

        if (0 === $decimalLength) {
            $number .= '.';
        }

        return str_pad($number, strlen($number) + ($scale - $decimalLength), '0', STR_PAD_RIGHT);
    }

    protected static function parseNumber(string $number): string
    {
        $number = str_replace('+', '', filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        if ('-0' === $number || !is_numeric($number)) {
            return '0';
        }

        return $number;
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int|null $scale
     * @return string
     */
    public static function add(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcadd($leftOperand, $rightOperand);
        } else {
            $r = bcadd($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    /**
     * @param string $number
     * @return string
     */
    public static function exp(string $number): string
    {
        $scale = static::DEFAULT_SCALE;
        $result = '1';
        for ($i = 299; $i > 0; --$i) {
            $result = static::add(static::mul(static::div($result, (string)$i, $scale), $number, $scale), '1', $scale);
        }

        return $result;
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int|null $scale
     * @return string
     */
    public static function mul(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcmul($leftOperand, $rightOperand);
        } else {
            $r = bcmul($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    /**
     * @param string $dividend
     * @param string $divisor
     * @param int|null $scale
     * @return string
     */
    public static function div(string $dividend, string $divisor, ?int $scale = null): string
    {
        $dividend = static::convertScientificNotationToString($dividend);
        $divisor = static::convertScientificNotationToString($divisor);

        if ('0' === static::trimTrailingZeroes($divisor)) {
            throw new InvalidArgumentException('Division by zero');
        }

        if (null === $scale) {
            $r = bcdiv($dividend, $divisor);
        } else {
            $r = bcdiv($dividend, $divisor, $scale);
        }

        if (null === $r) {
            throw new UnexpectedValueException('bcdiv should not return null!');
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    /**
     * @param string $number
     * @return string
     */
    public static function log(string $number): string
    {
        $number = static::convertScientificNotationToString($number);
        if ($number === '0') {
            return '-INF';
        }
        if (static::COMPARE_RIGHT_GRATER === static::comp($number, '0')) {
            return 'NAN';
        }
        $scale = static::DEFAULT_SCALE;
        $m = (string)log((float)$number);
        $x = static::sub(static::div($number, static::exp($m), $scale), '1', $scale);
        $res = '0';
        $pow = '1';
        $i = 1;
        do {
            $pow = static::mul($pow, $x, $scale);
            $sum = static::div($pow, (string)$i, $scale);
            if ($i % 2 === 1) {
                $res = static::add($res, $sum, $scale);
            } else {
                $res = static::sub($res, $sum, $scale);
            }
            ++$i;
        } while (static::comp($sum, '0', $scale));

        return static::add($res, $m, $scale);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int|null $scale
     * @return int
     */
    public static function comp(string $leftOperand, string $rightOperand, ?int $scale = null): int
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bccomp($leftOperand, $rightOperand, max(strlen($leftOperand), strlen($rightOperand)));
        }

        return bccomp(
            $leftOperand,
            $rightOperand,
            $scale
        );
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int|null $scale
     * @return string
     */
    public static function sub(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcsub($leftOperand, $rightOperand);
        } else {
            $r = bcsub($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    /**
     * @param bool $flag
     */
    public static function setTrimTrailingZeroes(bool $flag): void
    {
        self::$trimTrailingZeroes = $flag;
    }

    /**
     * @param mixed ...$values
     * @return string|null
     */
    public static function max(...$values): ?string
    {
        $max = null;
        foreach (static::parseValues($values) as $number) {
            $number = static::convertScientificNotationToString((string)$number);
            if (null === $max) {
                $max = $number;
            } elseif (static::comp($max, $number) === static::COMPARE_RIGHT_GRATER) {
                $max = $number;
            }
        }

        return $max;
    }

    protected static function parseValues(array $values): array
    {
        if (is_array($values[0])) {
            $values = $values[0];
        }

        return $values;
    }

    /**
     * @param mixed ...$values
     * @return string|null
     */
    public static function min(...$values): ?string
    {
        $min = null;
        foreach (static::parseValues($values) as $number) {
            $number = static::convertScientificNotationToString((string)$number);
            if (null === $min) {
                $min = $number;
            } elseif (static::comp($min, $number) === static::COMPARE_LEFT_GRATER) {
                $min = $number;
            }
        }

        return $min;
    }

    /**
     * @param string $base
     * @param string $exponent
     * @param string $modulus
     * @param int|null $scale
     * @return string
     */
    public static function powMod(
        string $base,
        string $exponent,
        string $modulus,
        ?int $scale = null
    ): string {
        $base = static::convertScientificNotationToString($base);
        $exponent = static::convertScientificNotationToString($exponent);

        if (static::isNegative($exponent)) {
            throw new InvalidArgumentException('Exponent can\'t be negative');
        }

        if ('0' === static::trimTrailingZeroes($modulus)) {
            throw new InvalidArgumentException('Modulus can\'t be zero');
        }

        // bcpowmod don't support floats
        if (
            static::isFloat($base) ||
            static::isFloat($exponent) ||
            static::isFloat($modulus)
        ) {
            $r = static::mod(static::pow($base, $exponent, $scale), $modulus, $scale);
        } elseif (null === $scale) {
            $r = bcpowmod($base, $exponent, $modulus);
        } else {
            $r = bcpowmod($base, $exponent, $modulus, $scale);
        }

        if (null === $r) {
            throw new UnexpectedValueException('bcpowmod should not return null!');
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    protected static function isNegative(string $number): bool
    {
        return 0 === strncmp('-', $number, 1);
    }

    /**
     * @param string $dividend
     * @param string $divisor
     * @param int|null $scale
     * @return string
     */
    public static function mod(string $dividend, string $divisor, ?int $scale = null): string
    {
        $dividend = static::convertScientificNotationToString($dividend);

        // bcmod in 7.2 is not working properly - for example bcmod(9.9999E-10, -0.00056, 9) should return '-0.000559999' but returns 0.0000000
        // let use this $x - floor($x/$y) * $y;
        return static::formatTrailingZeroes(
            static::sub(
                $dividend,
                static::mul(static::floor(static::div($dividend, $divisor, $scale)), $divisor, $scale),
                $scale
            ),
            $scale
        );
    }

    /**
     * @param string $number
     * @return string
     */
    public static function floor(string $number): string
    {
        $number = static::trimTrailingZeroes(static::convertScientificNotationToString($number));
        if (static::isFloat($number)) {
            $result = 0;
            if (static::isNegative($number)) {
                --$result;
            }
            $number = static::add($number, (string)$result, 0);
        }

        return static::parseNumber($number);
    }

    /**
     * @param string $number
     * @return string
     */
    public static function fact(string $number): string
    {
        $number = static::convertScientificNotationToString($number);

        if (static::isFloat($number)) {
            throw new InvalidArgumentException('Number has to be an integer');
        }
        if (static::isNegative($number)) {
            throw new InvalidArgumentException('Number has to be greater than or equal to 0');
        }

        $return = '1';
        for ($i = 2; $i <= $number; ++$i) {
            $return = static::mul($return, (string)$i);
        }

        return $return;
    }

    /**
     * @param string $hex
     * @return string
     */
    public static function hexdec(string $hex): string
    {
        $remainingDigits = substr($hex, 0, -1);
        $lastDigitToDecimal = (string)hexdec(substr($hex, -1));

        if ('' === $remainingDigits) {
            return $lastDigitToDecimal;
        }

        return static::add(static::mul('16', static::hexdec($remainingDigits)), $lastDigitToDecimal, 0);
    }

    /**
     * @param string $decimal
     * @return string
     */
    public static function dechex(string $decimal): string
    {
        $quotient = static::div($decimal, '16', 0);
        $remainderToHex = dechex((int)static::mod($decimal, '16'));

        if (static::comp($quotient, '0') === static::COMPARE_EQUAL) {
            return $remainderToHex;
        }

        return static::dechex($quotient) . $remainderToHex;
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @return string
     */
    public static function bitAnd(
        string $leftOperand,
        string $rightOperand
    ): string {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_AND);
    }

    protected static function bitOperatorHelper(string $leftOperand, string $rightOperand, string $operator): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (static::isFloat($leftOperand)) {
            throw new InvalidArgumentException('Left operator has to be an integer');
        }
        if (static::isFloat($rightOperand)) {
            throw new InvalidArgumentException('Right operator has to be an integer');
        }

        $leftOperandNegative = static::isNegative($leftOperand);
        $rightOperandNegative = static::isNegative($rightOperand);

        $leftOperand = static::dec2bin(static::abs($leftOperand));
        $rightOperand = static::dec2bin(static::abs($rightOperand));

        $maxLength = max(strlen($leftOperand), strlen($rightOperand));

        $leftOperand = static::alignBinLength($leftOperand, $maxLength);
        $rightOperand = static::alignBinLength($rightOperand, $maxLength);

        if ($leftOperandNegative) {
            $leftOperand = static::recalculateNegative($leftOperand);
        }
        if ($rightOperandNegative) {
            $rightOperand = static::recalculateNegative($rightOperand);
        }

        $isNegative = false;
        $result = '';
        if (static::BIT_OPERATOR_AND === $operator) {
            $result = $leftOperand & $rightOperand;
            $isNegative = ($leftOperandNegative and $rightOperandNegative);
        } elseif (static::BIT_OPERATOR_OR === $operator) {
            $result = $leftOperand | $rightOperand;
            $isNegative = ($leftOperandNegative or $rightOperandNegative);
        } elseif (static::BIT_OPERATOR_XOR === $operator) {
            $result = $leftOperand ^ $rightOperand;
            $isNegative = ($leftOperandNegative xor $rightOperandNegative);
        }

        if ($isNegative) {
            $result = static::recalculateNegative($result);
        }

        $result = static::bin2dec($result);

        return $isNegative ? '-' . $result : $result;
    }

    /**
     * @param string $number
     * @param int $base
     * @return string
     */
    public static function dec2bin(string $number, int $base = self::MAX_BASE): string
    {
        return static::decBaseHelper(
            $base,
            static function (int $base) use ($number) {
                $value = '';
                if ('0' === $number) {
                    return chr((int)$number);
                }

                while (BC::comp($number, '0') !== BC::COMPARE_EQUAL) {
                    $rest = BC::mod($number, (string)$base);
                    $number = BC::div($number, (string)$base);
                    $value = chr((int)$rest) . $value;
                }

                return $value;
            }
        );
    }

    protected static function decBaseHelper(int $base, Closure $closure): string
    {
        if ($base < 2 || $base > static::MAX_BASE) {
            throw new InvalidArgumentException('Invalid Base: ' . $base);
        }
        $orgScale = static::getScale();
        static::setScale(0);

        $value = $closure($base);

        static::setScale($orgScale);

        return $value;
    }

    /**
     * @param int $scale
     */
    public static function setScale(int $scale): void
    {
        bcscale($scale);
    }

    /**
     * @param string $number
     * @return string
     */
    public static function abs(string $number): string
    {
        $number = static::convertScientificNotationToString($number);

        if (static::isNegative($number)) {
            $number = (string)substr($number, 1);
        }

        return static::parseNumber($number);
    }

    protected static function alignBinLength(string $string, int $length): string
    {
        return str_pad($string, $length, static::dec2bin('0'), STR_PAD_LEFT);
    }

    protected static function recalculateNegative(string $number): string
    {
        $xor = str_repeat(static::dec2bin((string)(static::MAX_BASE - 1)), strlen($number));
        $number ^= $xor;
        for ($i = strlen($number) - 1; $i >= 0; --$i) {
            $byte = ord($number[$i]);
            if (++$byte !== static::MAX_BASE) {
                $number[$i] = chr($byte);
                break;
            }
        }

        return $number;
    }

    /**
     * @param string $binary
     * @param int $base
     * @return string
     */
    public static function bin2dec(string $binary, int $base = self::MAX_BASE): string
    {
        return static::decBaseHelper(
            $base,
            static function (int $base) use ($binary) {
                $size = strlen($binary);
                $return = '0';
                for ($i = 0; $i < $size; ++$i) {
                    $element = ord($binary[$i]);
                    $power = BC::pow((string)$base, (string)($size - $i - 1));
                    $return = BC::add($return, BC::mul((string)$element, $power));
                }

                return $return;
            }
        );
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @return string
     */
    public static function bitOr(string $leftOperand, string $rightOperand): string
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_OR);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @return string
     */
    public static function bitXor(string $leftOperand, string $rightOperand): string
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_XOR);
    }

    /**
     * @param string $number
     * @param int $precision
     * @return string
     */
    public static function roundHalfEven(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        if (!static::isFloat($number)) {
            return static::parseNumber($number);
        }

        $precessionPos = strpos($number, '.') + $precision + 1;
        if (strlen($number) <= $precessionPos) {
            return static::round($number, $precision);
        }

        if ($number[$precessionPos] !== '5') {
            return static::round($number, $precision);
        }

        $isPrevEven = $number[$precessionPos - 1] === '.'
            ? (int)$number[$precessionPos - 2] % 2 === 0
            : (int)$number[$precessionPos - 1] % 2 === 0;
        $isNegative = static::isNegative($number);

        if ($isPrevEven === $isNegative) {
            return static::roundUp($number, $precision);
        }

        return static::roundDown($number, $precision);
    }

    /**
     * @param string $number
     * @param int $precision
     * @return string
     */
    public static function round(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        if (static::isFloat($number)) {
            if (static::isNegative($number)) {
                return static::sub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return static::add($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return static::parseNumber($number);
    }

    /**
     * @param string $number
     * @param int $precision
     * @return string
     */
    public static function roundUp(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        $multiply = static::pow('10', (string)abs($precision));

        return $precision < 0
            ?
            static::mul(
                static::ceil(static::div($number, $multiply, static::getDecimalsLength($number))),
                $multiply,
                $precision
            )
            :
            static::div(
                static::ceil(static::mul($number, $multiply, static::getDecimalsLength($number))),
                $multiply,
                $precision
            );
    }

    /**
     * @param string $number
     * @return string
     */
    public static function ceil(string $number): string
    {
        $number = static::trimTrailingZeroes(static::convertScientificNotationToString($number));
        if (static::isFloat($number)) {
            $result = 1;
            if (static::isNegative($number)) {
                --$result;
            }
            $number = static::add($number, (string)$result, 0);
        }

        return static::parseNumber($number);
    }

    /**
     * @param string $number
     * @param int $precision
     * @return string
     */
    public static function roundDown(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        $multiply = static::pow('10', (string)abs($precision));

        return $precision < 0
            ?
            static::mul(
                static::floor(static::div($number, $multiply, static::getDecimalsLength($number))),
                $multiply,
                $precision
            )
            :
            static::div(
                static::floor(static::mul($number, $multiply, static::getDecimalsLength($number))),
                $multiply,
                $precision
            );
    }
}
