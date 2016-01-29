<?php
// plugin needs to work on Nucleus versions <=2.0 as well

class NP_SimpleParagraphs extends NucleusPlugin {

	function getName() {	// name of plugin
		return 'SimpleParagraphs';
	}

	function getAuthor()  {	// author of plugin
		return 'tyada';
	}

	function getURL() 	{	// an URL to the plugin website
		return 'http://www.tyada.com/';
	}

	function getVersion() {	// version of the plugin
		return '0.2';
	}

	// a description to be shown on the installed plugins listing
	function getDescription() {
		return 'This plug-in marks plain text up as paragraphs. Text lines, except marked up as block elements with (X)HTML in prior, are simply enclosed in <p> and </p> from their first letters to the next duplicated line feed or the end of the text.';
	}

	function supportsFeature($what) {
		switch($what){
			case 'SqlTablePrefix':
				return 1;
			default:
				return 0;
		}
	}

	function getEventList() { return array('PreItem'); }

	function event_PreItem(&$data) {
		$this->currentItem = &$data["item"];
		$this->markup_paragraphs($this->currentItem->body);
		if($this->currentItem->more)
			$this->markup_paragraphs($this->currentItem->more);
	}

	function markup_paragraphs(&$proc_text) {
		$text = $proc_text;
		$proc_text = "";

		$blockOpenTagPattern = '/<(p|pre|h[1-6]|address|blockquote|ol|ul|dl|form|table|hr)\b[^>]*>/i';
		$doubleLineFeeds = "\n\n";
		$blockOpenTagPatterns = array(
			'/<p\b[^>]*>/i',
			'/<pre\b[^>]*>/i',
			'/<h1\b[^>]*>/i',
			'/<h2\b[^>]*>/i',
			'/<h3\b[^>]*>/i',
			'/<h4\b[^>]*>/i',
			'/<h5\b[^>]*>/i',
			'/<h6\b[^>]*>/i',
			'/<address\b[^>]*>/i',
			'/<blockquote\b[^>]*>/i',
			'/<ol\b[^>]*>/i',
			'/<ul\b[^>]*>/i',
			'/<dl\b[^>]*>/i',
			'/<form\b[^>]*>/i',
			'/<table\b[^>]*>/i',
			'/<hr\b[^>]*>/i'
		);
		$blockCloseTagPatterns = array(
			'/<\/p>/i',
			'/<\/pre>/i',
			'/<\/h1>/i',
			'/<\/h2>/i',
			'/<\/h3>/i',
			'/<\/h4>/i',
			'/<\/h5>/i',
			'/<\/h6>/i',
			'/<\/address>/i',
			'/<\/blockquote>/i',
			'/<\/ol>/i',
			'/<\/ul>/i',
			'/<\/dl>/i',
			'/<\/form>/i',
			'/<\/table>/i',
			'/<hr\b[^>]*>/i'
		);
		$blockCloseTags = array(
			'</p>',
			'</pre>',
			'</h1>',
			'</h2>',
			'</h3>',
			'</h4>',
			'</h5>',
			'</h6>',
			'</address>',
			'</blockquote>',
			'</ol>',
			'</ul>',
			'</dl>',
			'</form>',
			'</table>',
			''
		);

		$text = preg_replace("/(\r\n|\n|\r)/", "\n", $text);
		$text = preg_replace("/\n\n+/", $doubleLineFeeds, $text);

		while (1) {
			if (preg_match($blockOpenTagPattern, $text, $matches)) {
				if (strpos($text, $matches[0])
					&& strlen(trim(substr($text, 0, strpos($text, $matches[0]))))) {
//					$tmptext = ltrim(substr($text, 0, strpos($text, $matches[0])));
					$proc_text .= $this->markup_plaintext(ltrim(substr($text, 0, strpos($text, $matches[0]))));
				}
				$text = strstr($text, $matches[0]);
				for ($i=0;$i<=count($blockOpenTagPatterns);$i++) {
					if (preg_match($blockOpenTagPatterns[$i], $matches[0])) {
						if (preg_match($blockCloseTagPatterns[$i], $text, $matches)) {
							$proc_text .= substr($text, 0, strpos($text, $matches[0])).$matches[0]."\n";
							$text = substr(strstr($text, $matches[0]), strlen($matches[0]));
						} else {
							$proc_text .= $text.$blockCloseTags[$i]."\n";
							$text = "";
						}
						break;
					}
				}
			} else {
				if (strlen(trim($text)))
					$proc_text .= $this->markup_plaintext(trim($text));
				break;
			}
		}
	}

	function markup_plaintext($data) {
		$doubleLineFeeds = "\n\n";
		$tmpText = $data;
		$return_text = "";

		while ($pos = strpos($tmpText, $doubleLineFeeds)) {
//			$return_text .= "<p>".trim(substr($tmpText, 0, $pos))."</p>\n";
//			$return_text .= "<p>".preg_replace("/\n/", "<br />\n", trim(substr($tmpText, 0, $pos)))."</p>\n";
			$tmpLines = explode("\n", trim(substr($tmpText, 0, $pos)));
			$return_text .= "<p>";
			for ($i=0;$i<count($tmpLines)-1;$i++) {
				if (preg_match("/<br.*>$/i", rtrim($tmpLines[$i])))
					$return_text .= $tmpLines[$i]."\n";
				else
					$return_text .= $tmpLines[$i]."<br />\n";
			}
			$return_text .= $tmpLines[count($tmpLines)-1]."</p>\n";
			$tmpText = substr(strstr($tmpText, $doubleLineFeeds), strlen($doubleLineFeeds));
		}
		if (strlen(trim($tmpText))) {
//			$return_text .= "<p>".trim($tmpText)."</p>\n";
//			$return_text .= "<p>".preg_replace("/\n/", "<br />\n", trim($tmpText))."</p>\n";
			$tmpLines = explode("\n", trim($tmpText));
			$return_text .= "<p>";
			for ($i=0;$i<count($tmpLines)-1;$i++) {
				if (preg_match("/<br.*>$/i", rtrim($tmpLines[$i])))
					$return_text .= $tmpLines[$i]."\n";
				else
					$return_text .= $tmpLines[$i]."<br />\n";
			}
			$return_text .= $tmpLines[count($tmpLines)-1]."</p>\n";
		}

		return $return_text;
	}
}
?>