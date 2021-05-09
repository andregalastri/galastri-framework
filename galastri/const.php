<?php

const MATCH_START = 0x1;
const MATCH_END = 0x2;
const MATCH_ANY = 0x4;
const MATCH_EXACT = 0x8;
const CAMEL_CASE = 0x10;
const PASCAL_CASE = 0x20;
const VARDUMP_JSON_TYPE = 0x40;
const VARDUMP_HTML_TYPE = 0x80;
const KEY = 0x100;
const VALUE = 0x200;
const STOP = 0x400;
const DONT_STOP = 0x800;

const PERFORMANCE_ANALYSIS_LABEL = 'galastri';
const REDIRECT_IDENFITY_PROTOCOLS_REGEX = '/http(?:s)?:?|ftp:?|ssh:?|rdp:?|irc:?|file:?|urn:?/';
