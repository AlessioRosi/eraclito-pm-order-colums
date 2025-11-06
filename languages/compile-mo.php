<?php
/**
 * Simple PO to MO compiler
 * Run this script to compile .po files to .mo files
 * Usage: php compile-mo.php
 */

class PoToMoCompiler {
	public function compile($po_file, $mo_file) {
		if (!file_exists($po_file)) {
			die("PO file not found: $po_file\n");
		}

		$po_content = file_get_contents($po_file);
		$entries = $this->parse_po($po_content);
		$mo_content = $this->generate_mo($entries);

		file_put_contents($mo_file, $mo_content);
		echo "Compiled: $mo_file\n";
	}

	private function parse_po($content) {
		$entries = array();
		$lines = explode("\n", $content);
		$current_msgid = '';
		$current_msgstr = '';
		$in_msgid = false;
		$in_msgstr = false;

		foreach ($lines as $line) {
			$line = trim($line);

			if (empty($line) || $line[0] === '#') {
				if (!empty($current_msgid) && !empty($current_msgstr)) {
					$entries[$current_msgid] = $current_msgstr;
					$current_msgid = '';
					$current_msgstr = '';
				}
				$in_msgid = false;
				$in_msgstr = false;
				continue;
			}

			if (strpos($line, 'msgid ') === 0) {
				if (!empty($current_msgid) && !empty($current_msgstr)) {
					$entries[$current_msgid] = $current_msgstr;
				}
				$current_msgid = $this->extract_string($line);
				$current_msgstr = '';
				$in_msgid = true;
				$in_msgstr = false;
			} elseif (strpos($line, 'msgstr ') === 0) {
				$current_msgstr = $this->extract_string($line);
				$in_msgid = false;
				$in_msgstr = true;
			} elseif ($line[0] === '"') {
				if ($in_msgid) {
					$current_msgid .= $this->extract_string($line);
				} elseif ($in_msgstr) {
					$current_msgstr .= $this->extract_string($line);
				}
			}
		}

		if (!empty($current_msgid) && !empty($current_msgstr)) {
			$entries[$current_msgid] = $current_msgstr;
		}

		return $entries;
	}

	private function extract_string($line) {
		if (preg_match('/"(.*)"/s', $line, $matches)) {
			return stripcslashes($matches[1]);
		}
		return '';
	}

	private function generate_mo($entries) {
		$originals = array();
		$translations = array();

		foreach ($entries as $msgid => $msgstr) {
			if (empty($msgid)) continue; // Skip header
			$originals[] = $msgid;
			$translations[] = $msgstr;
		}

		$count = count($originals);
		$ids_offset = 28;
		$strs_offset = $ids_offset + $count * 8;

		$ids = '';
		$strs = '';
		$offsets = array();

		$id_offset = $strs_offset + $count * 8;
		$str_offset = $id_offset;

		foreach ($originals as $original) {
			$str_offset += strlen($original) + 1;
		}

		foreach ($originals as $i => $original) {
			$ids .= pack('V', strlen($original)) . pack('V', $id_offset);
			$offsets[$i] = $id_offset;
			$id_offset += strlen($original) + 1;
		}

		foreach ($translations as $i => $translation) {
			$strs .= pack('V', strlen($translation)) . pack('V', $str_offset);
			$str_offset += strlen($translation) + 1;
		}

		$content = pack('Vx12V2', 0x950412de, $count, $ids_offset, $strs_offset);
		$content .= $ids . $strs;

		foreach ($originals as $original) {
			$content .= $original . "\0";
		}

		foreach ($translations as $translation) {
			$content .= $translation . "\0";
		}

		return $content;
	}
}

// Compile Italian translation
$compiler = new PoToMoCompiler();
$compiler->compile(
	__DIR__ . '/payment-method-order-column-it_IT.po',
	__DIR__ . '/payment-method-order-column-it_IT.mo'
);

echo "Translation compilation complete!\n";
