<?php
namespace Flowstarters\OEmbed\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowstarters.OEmbed".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A video oEmbed resource
 */
class RichResource extends AbstractResource implements ResourceInterface {

	/**
	 * HTML code to embed the oEmbed resource
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * @var integer
	 */
	protected $width;

	/**
	 * @var integer
	 */
	protected $height;

	/**
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}

	/**
	 * @param integer $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}

	/**
	 * @return integer
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param integer $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}

	/**
	 * @return integer
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Return best available string representation of the oEmbed resource
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->html;
	}
}

?>