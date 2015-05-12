<?php

namespace 
{

	class MarkdownDocument 
	{
		const NOLINKS = 1;
		const NOIMAGE = 2;
		const NOPANTS = 4;
		const NOHTML = 8;
		const STRICT = 16;
		const TAGTEXT = 32;
		const NO_EXT = 64;
		const CDATA = 128;
		const NOSUPERSCRIPT = 256;
		const NORELAXED = 512;
		const NOTABLES = 1024;
		const NOSTRIKETHROUGH = 2048;
		const TOC = 4096;
		const ONE_COMPAT = 8192;
		const AUTOLINK = 16384;
		const SAFELINK = 32768;
		const NOHEADER = 65536;
		const TABSTOP = 131072;
		const NODIVQUOTE = 262144;
		const NOALPHALIST = 524288;
		const NODLIST = 1048576;
		const EMBED = 35;
		const EXTRA_FOOTNOTE = 2097152;

		protected function __construct() {
		}

		public static function createFromStream($markdown_stream, $flags = NULL) {
		}

		public static function createFromString($markdown_doc, $flags = NULL) {
		}

		final protected function initFromStream($markdown_stream, $flags = NULL) {
		}

		final protected function initFromString($markdown_doc, $flags = NULL) {
		}

		public function compile($flags = NULL) {
		}

		public function isCompiled() {
		}

		public function dumpTree($out_stream, $title = NULL) {
		}

		public static function transformFragment($markdown_fragment, $flags = NULL) {
		}

		public static function writeFragment($markdown_fragment, $out_stream, $flags = NULL) {
		}

		public function setReferencePrefix($prefix) {
		}

		public function getTitle() {
		}

		public function getAuthor() {
		}

		public function getDate() {
		}

		public function getHtml() {
		}

		public function writeHtml($markdown_outstream) {
		}

		public function writeXhtmlPage($markdown_outstream) {
		}

		public function getToc() {
		}

		public function getCss() {
		}

		public function writeToc($markdown_outstream) {
		}

		public function writeCss($markdown_outstream) {
		}

		public function setUrlCallback($callback) {
		}

		public function setAttributesCallback($callback) {
		}

	}
}

