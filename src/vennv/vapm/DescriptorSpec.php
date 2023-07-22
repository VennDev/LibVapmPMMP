<?php

/*
 * Copyright (c) 2023 VennV, CC0 1.0 Universal
 *
 * CREATIVE COMMONS CORPORATION IS NOT A LAW FIRM AND DOES NOT PROVIDE
 * LEGAL SERVICES. DISTRIBUTION OF THIS DOCUMENT DOES NOT CREATE AN
 * ATTORNEY-CLIENT RELATIONSHIP. CREATIVE COMMONS PROVIDES THIS
 * INFORMATION ON AN "AS-IS" BASIS. CREATIVE COMMONS MAKES NO WARRANTIES
 * REGARDING THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS
 * PROVIDED HEREUNDER, AND DISCLAIMS LIABILITY FOR DAMAGES RESULTING FROM
 * THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS PROVIDED
 * HEREUNDER.
 */

declare(strict_types = 1);

namespace vennv\vapm;

final class DescriptorSpec
{

    public const BASIC = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    public const IGNORE_STDIN = [
        0 => ['file', '/dev/null', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    public const IGNORE_STDOUT = [
        0 => ['pipe', 'r'],
        1 => ['file', '/dev/null', 'w'],
        2 => ['pipe', 'w']
    ];

    public const IGNORE_STDERR = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['file', '/dev/null', 'w']
    ];

    public const IGNORE_STDOUT_AND_STDERR = [
        0 => ['pipe', 'r'],
        1 => ['file', '/dev/null', 'w'],
        2 => ['file', '/dev/null', 'w']
    ];

    public const IGNORE_STDIN_AND_STDERR = [
        0 => ['file', '/dev/null', 'r'],
        1 => ['pipe', 'w'],
        2 => ['file', '/dev/null', 'w']
    ];

    public const IGNORE_STDIN_AND_STDOUT = [
        0 => ['file', '/dev/null', 'r'],
        1 => ['file', '/dev/null', 'w'],
        2 => ['pipe', 'w']
    ];

}