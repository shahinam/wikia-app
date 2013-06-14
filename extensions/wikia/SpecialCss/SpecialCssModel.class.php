<?php
class SpecialCssModel extends WikiaModel {
	/**
	 * @desc The article page name of CSS file of which content we display in the editor
	 */
	const CSS_FILE_NAME = 'Wikia.css';
	/**
	 * @desc The city_id of community wiki from which we pull blog posts data
	 */
	const COMMUNITY_CENTRAL_CITY_ID = 177;
	/**
	 * @desc User avatar size
	 */
	const USER_AVATAR_SIZE = 25;
	/**
	 * @desc The category of blogposts we pull data from
	 */
	//const UPDATES_CATEGORY = 'CSS_Updates';
	const UPDATES_CATEGORY = 'Technical_Updates';
	/**
	 * @desc The section number we pull content from
	 */
	const UPDATE_SECTION_IN_BLOGPOST = 2;
	/**
	 * @desc Regex pattern used to extract h3 tags; see: SpecialCssModel::removeHeadline() and SpecialCssModel::addAnchorToPostUrl()
	 */
	const MEDIAWIKI_H3_PATTERN = '/===[^=]+===[^=]\s*/';
	/**
	 * @desc Limit of characters per one post snippet
	 */
	const SNIPPET_CHAR_LIMIT = 150;
	const MEMC_KEY = 'css-chrome-updates';
	
	/**
	 * @var array List of skins for which we would like to use SpecialCss for editing css file
	 */
	public static $supportedSkins = array( 'oasis' );

	/**
	 * @desc Retruns css file name
	 * 
	 * @return string
	 */
	public function getCssFileName() {
		return self::CSS_FILE_NAME;
	}

	/**
	 * @desc Returns Wikia.css article's content
	 * 
	 * @return Return|string
	 */
	public function getCssFileContent() {
		$cssArticle = $this->getCssFileArticle( $this->getCssFileArticleId() );
		return $cssArticle->getContent();
	}

	/**
	 * @desc Returns Title instance for Wikia.css article or null
	 * 
	 * @return null|Title
	 */
	public function getCssFileTitle() {
		return Title::newFromText( $this->getCssFileName(), NS_MEDIAWIKI);
	}

	/**
	 * @desc Returns article id of Wikia.css or null
	 * 
	 * @return int|null
	 */
	public function getCssFileArticleId() {
		wfProfileIn(__METHOD__);
		
		$title = $this->getCssFileTitle();
		$articleId = null;
		
		if( $title instanceof Title ) {
			$articleId = $this->getCssFileTitle()->getArticleId();
		}

		wfProfileOut(__METHOD__);
		return $articleId;
	}

	/**
	 * @desc Returns an article instance from given article id
	 * 
	 * @param $articleId
	 * @return Article|null
	 */
	public function getCssFileArticle($articleId) {
		return Article::newFromID($articleId);
	}

	/**
	 * @desc Compares passed article id with Wikia.css article id
	 * 
	 * @param $articleId
	 * @return bool
	 */
	public function isWikiaCssArticle($articleId) {
		return ($articleId == $this->getCssFileArticleId() );
	}

	/**
	 * @desc Returns url for Special:CSS page
	 * 
	 * @param bool $full
	 * @return string
	 */
	public function getSpecialCssUrl($full = false) {
		wfProfileIn(__METHOD__);
		
		$title = $this->getSpecialCssTitle();
		if( !$full ) {
			$url = $title->getLocalURL();
		} else {
			$url = $title->getFullUrl();
		}

		wfProfileOut(__METHOD__);
		return $url;
	}

	/**
	 * @desc Returns Title instance for Special:CSS page
	 * 
	 * @return Title
	 */
	public function getSpecialCssTitle() {
		return SpecialPage::getTitleFor('CSS');
	}

	/**
	 * @desc Returns an array of 20 last CSS updates
	 *
	 * @param array $blogParams parameters for api to fetch blog data
	 * @param array $revisionsParams parameters for api to fetch revisions data including user info
	 * @return array
	 */
	public function getCssBlogData($blogParams = [], $revisionsParams = []) {
		$cssBlogs = WikiaDataAccess::cache(
			wfSharedMemcKey(self::MEMC_KEY),
			60 * 60 * 24,
			function () use ($blogParams, $revisionsParams) {
				$cssBlogs = [];
				$cssBlogsData = $this->getCssBlogApiData($blogParams);
				$ids = $this->getBlogsIds($cssBlogsData);
				$cssRevisionsData = $this->getCssRevisionsApiData($ids, $revisionsParams);

				if ( $cssBlogsData ) {
					foreach ( $cssBlogsData as $blog ) {
						$pageId = $blog['pageid'];
						$blogUser = $cssRevisionsData[$pageId]['revisions'][0]['user'];
						$blogTitle = GlobalTitle::newFromId($pageId, self::COMMUNITY_CENTRAL_CITY_ID, 'wikia');
						$userPage = GlobalTitle::newFromText($blogUser, NS_USER, self::COMMUNITY_CENTRAL_CITY_ID);
						$timestamp = $cssRevisionsData[$pageId]['revisions'][0]['timestamp'];
						$sectionText = $cssRevisionsData[$pageId]['revisions'][0]['*'];

						$cssBlogs[] = [
							'title' => $this->getCleanTitle( $blogTitle->getText() ),
							'url' => trim( $blogTitle->getFullURL() . $this->addAnchorToPostUrl( $sectionText ) ),
							'userAvatar' => AvatarService::renderAvatar( $blogUser, 25 ),
							'userUrl' => $userPage->getFullUrl(),
							'userName' => $blogUser,
							'timestamp' => $this->wg->Lang->date( wfTimestamp( TS_MW, $timestamp ) ),
							'text' => $this->getPostSnippet($blogTitle, $sectionText),
						];
					}
				}
				return $cssBlogs;
			}
		);

		return $cssBlogs;
	}

	/**
	 * @desc Removes wikitext H3, truncates $sectionText and parse wikitext
	 * 
	 * @param Title $blogTitle
	 * @param String $sectionText
	 * 
	 * @return String
	 */
	private function getPostSnippet($blogTitle, $sectionText) {
		$output = $this->removeHeadline( $sectionText );
		$output = $this->wg->Lang->truncate( $output, self::SNIPPET_CHAR_LIMIT, wfMessage( 'ellipsis' )->text() );
		$output = $this->getParsedText($output, $blogTitle);
		
		return $output;
	}

	/**
	 * @desc Gets first H3 tag content and makes an anchor of it if found
	 * 
	 * @param String $sectionText
	 * 
	 * @return string
	 */
	private function addAnchorToPostUrl($sectionText) {
		$anchor = '';
		$headlines = [];
		
		if( preg_match( self::MEDIAWIKI_H3_PATTERN, $sectionText, $headlines ) ) {
			$anchor = '#' . str_replace( ' ', '_', str_replace('=', '', $headlines[0]) );
		}
		
		return $anchor;
	}

	/**
	 * @desc Removes wikitext's H3 tags from given text
	 * 
	 * @param $text
	 * @return mixed
	 */
	private function removeHeadline($text) {
		return preg_replace(self::MEDIAWIKI_H3_PATTERN, '', $text);
	}

	/**
	 * @desc Parse given wiki text and returns the HTML output
	 * 
	 * @param String $text
	 * @param Title $title
	 * 
	 * @return mixed
	 */
	private function getParsedText( $text, $title ) {
		// should we use here $wgParser or ParserPool?
		$output = $this->wg->Parser->parse( $text, $title, new ParserOptions() ); /** @var ParserOutput $output */
		return $output->getText();
	}

	/**
	 * @desc Removes from given string first slash and the string before it
	 * 
	 * @param String $titleText
	 *
	 * @return string
	 */
	private function getCleanTitle($titleText) {
		$result = $titleText;
		$slashPosition = mb_strpos($titleText, '/');
		
		if( $slashPosition !== false ) {
			$slashPosition++;
			$result = mb_strcut( $titleText, $slashPosition );
		}
		
		return $result;
	}

	/**
	 * @desc Returns formatted timestamp
	 *
	 * @param $timestamp
	 * @return Mixed|string
	 */
	private function getFormattedTimestamp($timestamp) {
		return wfTimestamp(TS_ISO_8601, $timestamp);
	}

	/**
	 * @desc Returns information about 20 last CSS updates (page id, namespace and post title)
	 *
	 * @param $params
	 * @return array
	 */
	private function getCssBlogApiData($params) {
		$defaultParams = $this->getDefaultBlogParams();
		$params = array_merge($defaultParams, $params);
		$blogs = $this->getApiData($params);

		return isset( $blogs['query']['categorymembers'] ) ? $blogs['query']['categorymembers'] : [];
	}

	/**
	 * @desc Returns information about 20 last revisions of CSS updates (user name, timestamp, post content)
	 *
	 * @param $ids page ids
	 * @param $params
	 * @return array
	 */
	private function getCssRevisionsApiData($ids, $params) {
		$defaultParams = $this->getDefaultRevisionParams($ids);
		$params = array_merge($defaultParams, $params);
		$revisions = $this->getApiData($params);

		return isset( $revisions['query']['pages'] ) ? $revisions['query']['pages'] : [];
	}

	/**
	 * @desc Returns data from API based on parameters
	 *
	 * @param $params
	 * @return mixed
	 */
	private function getApiData($params) {
		global $wgDevelEnvironment;
		$dbName = 'wikia';

		if ( $wgDevelEnvironment ) {
			$dbName = 'community';
		}

		$data = ApiService::foreignCall($dbName, $params);

		return $data;
	}

	/**
	 * @desc Returns array with page ids based on blog data from api
	 *
	 * @param $cssBlogsJson
	 * @return string
	 */
	private function getBlogsIds($cssBlogsJson) {
		$pageIds = [];

		foreach ( $cssBlogsJson as $blog ) {
			$pageIds[] = $blog['pageid'];
		}

		$ids = implode('|', $pageIds);

		return $ids;
	}

	/**
	 * @desc Returns default parameters for fetching blog data
	 *
	 * @return array
	 */
	private function getDefaultBlogParams() {
		return [
			'format' => 'json',
			'action' => 'query',
			'list' => 'categorymembers',
			'cmtitle' => 'Category:' . self::UPDATES_CATEGORY,
			'cmlimit' => '20',
			'cmsort' => 'timestamp',
			'cmdir' => 'desc'
		];
	}

	/**
	 * @desc Returns default parameters for fetching revision data
	 *
	 * @param string $ids page ids separated by '|'
	 * @return array
	 */
	private function getDefaultRevisionParams($ids = '') {
		return [
			'format' => 'json',
			'action' => 'query',
			'prop' => 'revisions',
			'rvprop' => 'content|user|timestamp',
			'rvsection' => self::UPDATE_SECTION_IN_BLOGPOST,
			'pageids' => $ids
		];
	}
}
