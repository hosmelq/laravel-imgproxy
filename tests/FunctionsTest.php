<?php

declare(strict_types=1);

use function HosmelQ\Imgproxy\Laravel\imgproxy;

it('exposes the imgproxy helper', function (): void {
    expect(imgproxy()->format('webp')->build('https://example.com/helper.png'))
        ->toBe('https://imgproxy.test/insecure/format:webp/aHR0cHM6Ly9leGFtcGxlLmNvbS9oZWxwZXIucG5n.webp');
});
