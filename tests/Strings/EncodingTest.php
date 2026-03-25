<?php

namespace Joby\Toolbox\Strings;

use PHPUnit\Framework\TestCase;

class EncodingTest extends TestCase
{

    // round-trip tests
    public function testRoundTripEmptyString(): void
    {
        $this->assertSame('', Encoding::base64url_decode(Encoding::base64url_encode('')));
    }

    public function testRoundTripAscii(): void
    {
        $input = 'Hello, world!';
        $this->assertSame($input, Encoding::base64url_decode(Encoding::base64url_encode($input)));
    }

    public function testRoundTripBinaryData(): void
    {
        $input = random_bytes(64);
        $this->assertSame($input, Encoding::base64url_decode(Encoding::base64url_encode($input)));
    }

    // output format tests
    public function testOutputContainsNoPlusSign(): void
    {
        // brute-force across enough varied inputs to hit the + case
        for ($i = 0; $i < 100; $i++) {
            $encoded = Encoding::base64url_encode(random_bytes(32));
            $this->assertStringNotContainsString('+', $encoded);
        }
    }

    public function testOutputContainsNoSlash(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $encoded = Encoding::base64url_encode(random_bytes(32));
            $this->assertStringNotContainsString('/', $encoded);
        }
    }

    public function testOutputContainsNoPadding(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $encoded = Encoding::base64url_encode(random_bytes(32));
            $this->assertStringNotContainsString('=', $encoded);
        }
    }

    // known-value tests to catch character substitution regressions
    public function testKnownValueWithPlus(): void
    {
        // base64 of "\xfb" is "+w==" — should become "-w"
        $this->assertSame('-w', Encoding::base64url_encode("\xfb"));
    }

    public function testKnownValueWithSlash(): void
    {
        // base64 of "\xff" is "/w==" — should become "_w"
        $this->assertSame('_w', Encoding::base64url_encode("\xff"));
    }

    // padding edge cases (1, 2, 3 byte inputs hit all padding variants)
    public function testPaddingVariants(): void
    {
        foreach ([1, 2, 3] as $length) {
            $input = str_repeat('a', $length);
            $encoded = Encoding::base64url_encode($input);
            $this->assertStringNotContainsString('=', $encoded);
            $this->assertSame($input, Encoding::base64url_decode($encoded));
        }
    }

}
