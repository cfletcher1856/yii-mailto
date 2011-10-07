<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 *
 * Encodes mailto links to hinder bots from scraping email addresses.
 * @author Colin Fletcher
 * @copyright (C) Colin Fletcher 2011
 * @package Mailto
 * @version 1.0
 */
	class Mailto extends CApplicationComponent {
	
		/**
		 * The email address we are encoding
		 * @var string
		 */
		private static $_email;
		
		/**
		 * What is displayed to the end user on the site
		 * @var string
		 */
		private static $_link_text;
		
		/**
		 * The type of encoding that will be applied ("javascript", "javascript_charcode", "hex" or "none")
		 * @var string
		 */
		private static $_encoding;
		
		/**
		 * Holds all options that will be applied to the link or the email address
		 * @var array
		 */
		private static $_options;
		
		/**
		 * Holds attributes to apply to the mailto anchor element
		 * @var array
		 */
		private static $_linkOptions;
		
		/**
		 * Displays the email address encoded or not depending on the options passed in
		 * @param string $email
		 * @param string $encoding
		 * @param string $link_text
		 * @param array $options
		 */
		public function link($email, $encoding="none", $link_text="", $options=array()){
			if(!$email)
				throw new CException('Email required for Mailto');
			
			if(!eregi("^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$", $email))
				throw new CException('Invalid email format');
			
			self::initalize($email, $encoding, $link_text, $options);
			self::setOptions();
			
			try{
				self::$encoding();
			} catch (Exception $e){
				throw new CException('The supported encodings are "javascript", "javascript_charcode", "hex" or "none"');
			}
		}
		
		/**
		 * Sets all parameters to private variables within the class
		 * @param string $email
		 * @param string $encoding
		 * @param string $link_text
		 * @param array $options
		 */
		private static function initalize($email, $encoding, $link_text, $options){
			self::$_email = $email;
			self::$_link_text = (strlen($link_text)) ? $link_text : $email;
			self::$_encoding = $encoding;
			self::$_options = $options;
		}
		
		/**
		 * Loops through the options array and sets them to the proper variables where needed
		 */
		private static function setOptions(){
			$search = array('%40', '%2C');
			$replace = array('@', ',');
			
			if(!empty(self::$_options)){
				$emailOptions = "?";
				foreach(self::$_options as $k => $v){
					switch($k){
						case 'cc':
						case 'bcc':
						case 'followupto':
							$emailOptions .= $k . "=" . str_replace($search, $replace, rawurlencode($v)) . "&";
							break;
							
						case 'subject':
						case 'newsgroup':
							$emailOptions .= $k . "=" . rawurlencode($v) . "&";
							break;
						case 'class':
						case 'id':
						case 'name':
						case 'title':
							self::$_linkOptions .= $k . "=" . $v . " ";
							break;
						default:
					}
				}
				self::$_email .= rtrim($emailOptions, '&');
			}
		}
		
		/**
		 * Encodes the email address and all options using pure javascript
		 */
		private static function javascript(){
			$encode_me = "document.write('" . self::getPlainTextLink() . "');";
			$len = strlen($encode_me);
			
			for($i=0; $i<$len; $i++){
				$encoded .= "%" . bin2hex($encode_me[$i]);
			}
			
			echo "<script type=\"text/javascript\">eval(unescape('" . $encoded . "'));</script>";
		}
		
		/**
		 * Encodes the email address and all options using internal javascript functions
		 */
		private static function javascript_charcode(){
			$encode_me = self::getPlainTextLink();
			$len = strlen($encode_me);
			
			for($i=0; $i<$len; $i++){
				$ordArray[] = ord($encode_me[$i]);
			}
			
			echo "<script type=\"text/javascript\">document.write(String.fromCharCode(" . implode(',', $ordArray) . "));</script>";
		}
		
		/**
		 * Encodes the email address and all options using hex
		 */
		private static function hex(){
			$encode_me = self::$_email;
			$len = strlen($encode_me);
			
			for($i=0; $i<$len; $i++){
				$encoded_address .= (preg_match("/\w/", $encode_me[$i])) ? "%" . bin2hex($encode_me[$i]) : $encode_me[$i];
			}
			
			$encode_me = self::$_link_text;
			$len = strlen($encode_me);
			
			for($i=0; $i<$len; $i++){
				$encoded_link_text .= "&#x" . bin2hex($encode_me[$i]) . ";";
			}
			
			$mailto = "&#109;&#97;&#105;&#108;&#116;&#111;&#58;";
			echo "<a href=\"$mailto" . $encoded_address . "\" " . self::$_linkOptions . ">" . $encoded_link_text . "</a>";
		}
		
		/**
		 * Displays the mailto link with no encoding on it what so ever
		 */
		private static function none(){
			echo self::getPlainTextLink();
		}
		
		/**
		 * Displays the mailto link with no encoding on it what so ever.  This is a helper function for the other encoding functions
		 * @return string
		 */
		private static function getPlainTextLink(){
			return "<a href=\"mailto:" . self::$_email . "\" " . self::$_linkOptions . ">" . self::$_link_text . "</a>";
		}
	}