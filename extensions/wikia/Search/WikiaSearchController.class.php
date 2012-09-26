what<?php
class WikiaSearchController extends WikiaSpecialPageController {


	const RESULTS_PER_PAGE = 25;
	const PAGES_PER_WINDOW = 5;

	/**
	 * @var WikiaSearch
	 */
	protected $wikiaSearch = null;

	public function __construct() {
        // note: this is required since we haven't constructed $this->wg yet
		global $wgWikiaSearchIsDefault;

		$this->wikiaSearch = F::build('WikiaSearch');
		$specialPageName = $wgWikiaSearchIsDefault ? 'Search' : 'WikiaSearch';
		parent::__construct( $specialPageName, $specialPageName, false );
	}

	protected function  isCorporateWiki() {
		return !empty($this->wg->EnableWikiaHomePageExt);
	}

	public function index() {
		$this->wg->Out->addHTML( F::build('JSSnippets')->addToStack( array( "/extensions/wikia/Search/js/WikiaSearch.js" ) ) );
		$this->wg->SuppressRail = true;

		$skin = $this->wg->User->getSkin();
		$showSearchAds = false;
		if (!empty($this->wg->EnableWikiaSearchAds)) {
			if (!empty($this->wg->NoExternals)) {
				// don't show ads in search
			} elseif (is_object($this->wg->User) && $this->wg->User->isLoggedIn() && !($this->wg->User->getOption('showAds') || !empty($_GET['showads']))) {
				// don't show ads in search
			} elseif ((! $skin instanceof SkinMonoBook) && (! $skin instanceof SkinVector)) {
				$this->app->registerHook('MakeGlobalVariablesScript', 'WikiaSearchAdsController', 'onMakeGlobalVariablesScript');
				$this->response->addAsset('extensions/wikia/Search/js/WikiaSearchAds.js');
				$showSearchAds = true;
			}
		}

		if ( $skin instanceof SkinMonoBook ) {
			$this->response->addAsset('extensions/wikia/Search/monobook/monobook.scss');
		}
		if ( get_class($this->wg->User->getSkin()) == 'SkinOasis' ) {
			$this->response->addAsset('extensions/wikia/Search/css/WikiaSearch.scss');
		}

		$searchConfig = F::build('WikiaSearchConfig');
		
		$query = $this->getVal('query', $this->getVal('search'));
		$query = htmlentities( Sanitizer::StripAllTags ( $query ), ENT_COMPAT, 'UTF-8' );
		$searchConfig->setQuery( $query );
		
		$limit = $this->getVal('limit', self::RESULTS_PER_PAGE);
		$page = $this->getVal('page', 1);
		$rank = $this->getVal('rank', 'default');
		$debug = $this->request->getBool('debug', false);
		$crossWikia = $this->request->getBool('crossWikia', false);
		$skipCache = $this->request->getBool('skipCache', false);
		$advanced = $this->getVal( 'advanced', false );
		$hub = ($this->getVal('nohub') != '1') ? $this->getVal('hub', false) : false;
		$redirs = !empty($advanced) ? $this->request->getBool('redirs', false) : false;

		$searchableNamespaces = SearchEngine::searchableNamespaces();
		$namespaces = array();
		foreach($searchableNamespaces as $i => $name) {
			if ( $this->getVal('ns'.$i, false) ) {
				$namespaces[] = $i;
			}
		}
		if (empty($namespaces) && $this->wg->User->getOption('searchAllNamespaces')) {
			$namespaces = array_keys($searchableNamespaces);
		}

		//  Check for crossWikia value set in url.  Otherwise, check if we're on the corporate wiki
		$isInterWiki = $crossWikia ? true : $this->isCorporateWiki();

		if($this->isCorporateWiki()) {
			OasisController::addBodyClass('inter-wiki-search');
		}

		$results = false;
		$resultsFound = 0;
		$paginationLinks = '';
		if( !empty( $query ) ) {
			$articleMatch = null;
			if ( $page == 1 ) {
				$articleMatch = $this->wikiaSearch->getArticleMatch($searchConfig);
				
				if (!empty($articleMatch) && $this->getVal('fulltext', '0') === '0') {
	
					$article = isset($articleMatch['redirect']) ? $articleMatch['redirect'] : $articleMatch['article'];
					$title = $article->getTitle();
	
					wfRunHooks( 'SpecialSearchIsgomatch', array( &$title, $query ) );
	
					Track::event( 'search_start_gomatch', array( 'sterm' => $query, 'rver' => 0 ) );
					$this->response->redirect( $title->getFullURL() );
				}
				elseif(!empty($articleMatch)) {
					Track::event( 'search_start_match', array( 'sterm' => $query, 'rver' => 0 ) );
				} else {
					$title = Title::newFromText( $query );
					if ( !is_null( $title ) ) {
						wfRunHooks( 'SpecialSearchNogomatch', array( &$title ) );
					}
				}
			}

			$isGrouped = $isInterWiki || $this->getVal('grouped', false);

			$searchConfig->setLength			( $limit )
						 ->setPage				( $page )
						 ->setRank				( $rank )
						 ->setCityId			( $isInterWiki ? 0 : $this->wg->CityId )
						 ->setGroupResults		( $isGrouped )
						 ->setHub				( $hub )
						 ->setVideoSearch		( $this->getVal('videoSearch', false) )
						 ->setSkipCache			( $skipCache )
						 ->setIncludeRedirects	( $redirs )
						 ->setNamespaces		( $namespaces )
						 ->setArticleMatch		( $articleMatch )
						 ->setDebug				( $debug )
						 ->setAdvanced			( $advanced )
						 ->setIsInterWiki		( $isInterWiki )
			;

			$results = $this->wikiaSearch->doSearch( $query, $searchConfig );

			$resultsFound = $results->getResultsFound();
			$searchConfig->setResultsFound($resultsFound);

			if(!empty($resultsFound)) {
				$paginationParams = array('config' => $searchConfig);
				$paginationLinks = $this->sendSelfRequest( 	'pagination',  $paginationParams);
			}

			$this->app->wg->Out->setPageTitle( $this->wf->msg( 'wikiasearch2-page-title-with-query', array(ucwords($query), $this->wg->Sitename) )  );
		} else {
			if($isInterWiki) {
				$this->app->wg->Out->setPageTitle( $this->wf->msg( 'wikiasearch2-page-title-no-query-interwiki' ) );
			} else {
				$this->app->wg->Out->setPageTitle( $this->wf->msg( 'wikiasearch2-page-title-no-query-intrawiki', array($this->wg->Sitename) )  );
			}
		}

		$activeTab = $this->getActiveTab( $namespaces );

		if(!$isInterWiki) {
			$advancedParams = array( 'config' => $searchConfig );
			$advancedSearchBox = $this->sendSelfRequest( 'advancedBox', $advancedParams );
			$this->setval( 'advancedSearchBox', $advancedSearchBox );
		}

        if ( $this->app->checkSkin( 'wikiamobile' ) ) {
            $this->overrideTemplate( 'WikiaMobileIndex' );
        }

		/*
		 * Done to return results in json format
		 * Can be removed after upgrade to 5.4 and specify serialized Json data on WikiaSearchResult
		 * http://php.net/manual/en/jsonserializable.jsonserialize.php
		*/
		$format = $this->response->getFormat();
		if( ($format == 'json' || $format == 'jsonp') && count( $results ) ){
			$tempResults = array();
			foreach( $results as $result ){
				if($result instanceof WikiaSearchResult){
					$tempResults[] = $result->toArray(array('title', 'url'));
				}
			}
			$results = $tempResults;
		}

		$this->setVal( 'results', $results );
		$this->setVal( 'resultsFound', $resultsFound );
		$this->setVal( 'resultsFoundTruncated', $this->wg->Lang->formatNum( $searchConfig->getTruncatedResultsNum() ) );
		$this->setVal( 'isOneResultsPageOnly', ( $resultsFound <= $limit ) );
		$this->setVal( 'pagesCount', ceil($resultsFound/$limit) );
		$this->setVal( 'currentPage',  $page );
		$this->setVal( 'paginationLinks', $paginationLinks );
		$this->setVal( 'tabs', $this->sendSelfRequest( 'tabs', array( 'config' => $searchConfig, 'activeTab' => $activeTab) ) );
		$this->setVal( 'query', $query );
		$this->setVal( 'resultsPerPage', $this->getVal('limit', $limit) );
		$this->setVal( 'pageUrl', $this->wg->Title->getFullUrl() );
		$this->setVal( 'debug', $debug );
		$this->setVal( 'solrHost', $this->wg->SolrHost);
		$this->setVal( 'isInterWiki', $isInterWiki );
		$this->setVal( 'relevancyFunctionId', WikiaSearch::RELEVANCY_FUNCTION_ID );
		$this->setVal( 'namespaces', $namespaces );
		$this->setVal( 'hub', $hub );
		$this->setVal( 'hasArticleMatch', $searchConfig->hasArticleMatch() );
		$this->setVal( 'isMonobook', ($this->wg->User->getSkin() instanceof SkinMonobook) );
		$this->setVal( 'showSearchAds', $query ? $showSearchAds : false );
		$this->setVal( 'isCorporateWiki', $this->isCorporateWiki() );
	}

	public function advancedBox() {
		$config = $this->getVal('config');

		$this->setVal( 'term',  $config->getQuery() );
		$this->setVal( 'bareterm', $config->getQuery() ); // query is stored as bareterm in config
		$this->setVal( 'namespaces', $config->getNamespaces() );
		$this->setVal( 'searchableNamespaces', SearchEngine::searchableNamespaces() );
		$this->setVal( 'redirs', $config->getIncludeRedirects() );
		$this->setVal( 'advanced', $config->getAdvanced() );
	}

	public function tabs() {
		$config = $this->getVal('config');
		$activeTab = $this->getVal( 'activeTab' );
		$namespaces = $config->getNamespaces();

		$this->setVal( 'bareterm', $config->getQuery() );
		$this->setVal( 'searchProfiles', $config->getSearchProfiles() );
		$this->setVal( 'redirs', $config->getIncludeRedirects() );
		$this->setVal( 'activeTab', $activeTab );
	}

	public function advancedTabLink() {
		$term = $this->getVal('term');
		$namespaces = $this->getVal('namespaces');
		$label = $this->getVal('label');
		$tooltip = $this->getVal('tooltip');
		$params = $this->getVal('params');
		$redirs = $this->getVal('redirs');

		$opt = $params;
		foreach( $namespaces as $n ) {
			$opt['ns' . $n] = 1;
		}

		$opt['redirs'] = !empty($redirs) ? 1 : 0;
		$stParams = array_merge( array( 'search' => $term ), $opt );

		$title = F::build('SpecialPage', array( 'WikiaSearch' ), 'getTitleFor');

		$this->setVal( 'href', $title->getLocalURL( $stParams ) );
		$this->setVal( 'title', $tooltip );
		$this->setVal( 'label', $label );
		$this->setVal( 'tooltip', $tooltip );
	}


	protected function getSearchProfiles($namespaces) {
		// Builds list of Search Types (profiles)
		$nsAllSet = array_keys( SearchEngine::searchableNamespaces() );
		$profiles = array(
			'default' => array(
				'message' => 'wikiasearch2-tabs-articles',
				'tooltip' => 'searchprofile-articles-tooltip',
				'namespaces' => SearchEngine::defaultNamespaces(),
				'namespace-messages' => SearchEngine::namespacesAsText(
					SearchEngine::defaultNamespaces()
				),
			),
			'images' => array(
				'message' => 'wikiasearch2-tabs-photos-and-videos',
				'tooltip' => 'searchprofile-images-tooltip',
				'namespaces' => array( NS_FILE ),
			),
			'users' => array(
				'message' => 'wikiasearch2-users',
				'tooltip' => 'wikiasearch2-users-tooltip',
				'namespaces' => array( NS_USER )
			),
			'all' => array(
				'message' => 'searchprofile-everything',
				'tooltip' => 'searchprofile-everything-tooltip',
				'namespaces' => $nsAllSet,
			),
			'advanced' => array(
				'message' => 'searchprofile-advanced',
				'tooltip' => 'searchprofile-advanced-tooltip',
				'namespaces' => $namespaces,
				'parameters' => array( 'advanced' => 1 ),
			)
		);

		$this->wf->RunHooks( 'SpecialSearchProfiles', array( &$profiles ) );

		foreach( $profiles as $key => &$data ) {
			sort($data['namespaces']);
		}

		return $profiles;
	}

	protected function getActiveTab( $namespaces ) {
		if($this->request->getVal('advanced')) {
			return 'advanced';
		}

		$searchableNamespaces = array_keys( SearchEngine::searchableNamespaces() );
		$nsVals = array();

		foreach($searchableNamespaces as $ns) {
			if ($val = $this->request->getVal('ns'.$ns)) {
				$nsVals[] = $ns;
			}
		}

		if(empty($nsVals)) {
			return $this->wg->User->getOption('searchAllNamespaces') ? 'all' :  'default';
		}

		foreach( $this->getSearchProfiles( $namespaces ) as $name => $profile ) {
			if ( !count( array_diff( $nsVals, $profile['namespaces'] ) ) && !count( array_diff($profile['namespaces'], $nsVals ) )) {
				return $name;
			}
		}

		return 'advanced';
	}


	public function pagination() {
		$config = $this->getVal('config');
		
		$query = $config->getQuery();
		$page = $config->getPage();
		$resultsCount = $config->getResultsFound();
		$limit = $config->getLimit();
		$pagesNum = ceil( $resultsCount / $limit );

		$crossWikia = $config->getIsInterwiki();
		$debug = $config->getDebug();
		$skipCache = $config->getSkipCache();
		$namespaces = $config->getNamespaces();
		$advanced = $config->getAdvanced();
		$redirs = $config->getIncludeRedirects();
		
		$windowFirstPage = ( ( ( $page - self::PAGES_PER_WINDOW ) > 0 ) ? ( $page - self::PAGES_PER_WINDOW ) : 1 );
		$windowLastPage = ( ( ( $page + self::PAGES_PER_WINDOW ) < $pagesNum ) ? ( $page + self::PAGES_PER_WINDOW ) : $pagesNum ) ;

		$this->setVal( 'query', $query );
		$this->setVal( 'pagesNum', $pagesNum );
		$this->setVal( 'currentPage', $page );
		$this->setVal( 'windowFirstPage', $windowFirstPage );
		$this->setVal( 'windowLastPage', $windowLastPage );
		$this->setVal( 'pageTitle', $this->wg->Title );
		$this->setVal( 'crossWikia', $crossWikia );
		$this->setVal( 'resultsCount', $resultsCount );
		$this->setVal( 'skipCache', $skipCache );
		$this->setVal( 'debug', $debug );
		$this->setVal( 'namespaces', $namespaces );
		$this->setVal( 'advanced', $advanced );
		$this->setVal( 'redirs', $redirs );
		$this->setVal( 'limit', $limit );
	}

	public function getPage() {
		$pageId = $this->getVal('id');
		$metaData = $this->getVal('meta', true);

		if( !empty( $pageId ) ) {
			$page = $this->wikiaSearch->getPage( $pageId, $metaData );

			$this->response->setData( $page );
		}

		// force output format as there's no template file (BugId:18831)
		$this->getResponse()->setFormat('json');
	}

	public function getPages() {
	  $this->wg->AllowMemcacheWrites = false;
	  $ids = $this->getVal('ids');
	  $metaData = $this->getVal('meta', true);

	  if ( !empty( $ids ) ) {
	    $this->response->setData( $this->wikiaSearch->getPages($ids) );
	  }
	  $this->getResponse()->setFormat('json');

	}

	public function getPageMetaData() {
		$pageId = $this->getVal('id');

		if( !empty( $pageId ) ) {
			$metaData = $this->wikiaSearch->getPageMetaData( $pageId );

			$this->response->setData( $metaData );
		}
	}

	public function getRelatedVideos() {
		$searchConfig = F::build('WikiaSearchConfig');
		$pageId = $this->getVal('id');
		if ( !empty( $pageId ) ) {
			$searchConfig->setPageId( $pageId );
		}
		$searchConfig
			->setStart	(  0 )
			->setSize	( 20 );
		
		$responseData = $this->wikiaSearch->getRelatedVideos( $searchConfig );
		$this->response->setData($responseData);
		$this->response->setFormat('json');
	}

	public function getSimilarPagesExternal() {
		$searchConfig = F::build('WikiaSearchConfig');
		$url = $this->getVal('url', null);
		$contents = $this->getVal('contents', null); 
		if ( $url !== null ) {
			$searchConfig->setContentUrl($url);
		} else if ( $contents !== null ) {
			$searchConfig->setStreamBody($contents);
		} else {
			throw new Exception('Please provide a url or stream contents');
		}
		
		$responseData = $this->wikiaSearch->getSimilarPages( $searchConfig );
		$this->response->setData($responseData);
		$this->response->setFormat('json');
	}

	public function getKeywords() {
		$searchConfig = F::build('WikiaSearchConfig');
		$searchConfig->setPageId($this->getVal('id', false));
		$responseData = $this->wikiaSearch->getKeywords( $searchConfig );
		$this->response->setData($responseData);
		$this->response->setFormat('json');
	}

	public function getTagCloud() {
		$params = $this->getTagCloudParams();

		$this->response->setData($this->wikiaSearch->getTagCloud($params));
		$this->response->setFormat('json');
	}

	private function getTagCloudParams()
	{
		$params = array();
		$params['maxpages']    = $this->getVal('maxpages', 25);
		$params['termcount']   = $this->getVal('termcount', 50);
		$params['maxfontsize'] = $this->getVal('maxfontsize', 56);
		$params['minfontsize'] = $this->getVal('minfontsize', 10);
		$params['sizetype']    = $this->getVal('sizetype', 'pt');
		return $params;
	}

	//WikiaMobile hook to add assets so they are minified and concatenated
	public function onWikiaMobileAssetsPackages( &$jsHeadPackages, &$jsBodyPackages, &$scssPackages){
		if( F::app()->wg->Title->isSpecial('Search') ) {
			$jsBodyPackages[] = 'wikiasearch_js_wikiamobile';
			$scssPackages[] = 'wikiasearch_scss_wikiamobile';
		}

		return true;
	}

	public function videoSearch()
	{
		$query = $this->getVal('q');

		$searchConfig = F::build('WikiaSearchConfig');
		$searchConfig
			->setCityId	($this->wg->cityId)
			->setQuery	($query)
		;
		

		$results = $this->wikiaSearch->searchVideos($query, $searchConfig);
		
		// up to whoever's using this service as to what they want from here. I'm just going to return JSON.
		// if you just want to search for only videos in the traditional video interface, then you should 
		// be setting 'videoSearch' in the query string of the search index page
		$processedResultArray = array();
		foreach ($results as $result) {
			$processedResultArray[] = (array) $result;
		}
		$this->getResponse()->setFormat('json');
		$this->getResponse()->setData( $processedResultArray );
		
	}
	
}
