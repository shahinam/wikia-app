/*!
 * VisualEditor user interface MWSettingsPage class.
 *
 * @copyright 2011-2014 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * MediaWiki meta dialog settings page.
 *
 * @class
 * @extends OO.ui.PageLayout
 *
 * @constructor
 * @param {string} name Unique symbolic name of page
 * @param {Object} [config] Configuration options
 */
ve.ui.MWSettingsPage = function VeUiMWSettingsPage( name, config ) {
	var settingsPage = this;

	// Parent constructor
	OO.ui.PageLayout.call( this, name, config );

	// Properties
	this.metaList = null;
	this.tocOptionTouched = false;
	this.redirectOptionsTouched = false;
	this.tableOfContentsTouched = false;
	this.label = ve.msg( 'visualeditor-dialog-meta-settings-section' );

	this.settingsFieldset = new OO.ui.FieldsetLayout( {
		'$': this.$,
		'label': ve.msg( 'visualeditor-dialog-meta-settings-label' ),
		'icon': 'settings'
	} );

	// Initialization

	// Table of Contents items
	this.tableOfContents = new OO.ui.FieldLayout(
		new OO.ui.ButtonSelectWidget( { '$': this.$ } )
			.addItems( [
				new OO.ui.ButtonOptionWidget(
					'mwTOCForce',
					{
						'label': ve.msg( 'visualeditor-dialog-meta-settings-toc-force' ),
						'flags': ['secondary']
					}
				),
				new OO.ui.ButtonOptionWidget(
					'default',
					{
						'label': ve.msg( 'visualeditor-dialog-meta-settings-toc-default' ),
						'flags': ['secondary']
					}
				),
				new OO.ui.ButtonOptionWidget(
					'mwTOCDisable',
					{
						'label': ve.msg( 'visualeditor-dialog-meta-settings-toc-disable' ),
						'flags': ['secondary']
					}
				)
			] )
			.connect( this, { 'select': 'onTableOfContentsFieldChange' } ),
		{
			'$': this.$,
			'align': 'top',
			'label': ve.msg( 'visualeditor-dialog-meta-settings-toc-label' )
		}
	);

	// Redirect items
	this.enableRedirectInput = new OO.ui.CheckboxInputWidget( { '$': this.$ } );
	this.enableRedirectField = new OO.ui.FieldLayout(
		this.enableRedirectInput,
		{
			'$': this.$,
			'align': 'inline',
			'label': ve.msg( 'visualeditor-dialog-meta-settings-redirect-label' )
		}
	);
	this.redirectTargetInput = new ve.ui.MWTitleInputWidget( {
		'$': this.$,
		'placeholder': ve.msg( 'visualeditor-dialog-meta-settings-redirect-placeholder' )
	} );
	this.redirectTargetField = new OO.ui.FieldLayout(
		this.redirectTargetInput,
		{
			'$': this.$,
			'align': 'top'
		}
	);
	this.enableRedirectInput.connect( this, { 'change': 'onEnableRedirectChange' } );
	this.redirectTargetInput.connect( this, { 'change': 'onRedirectTargetChange' } );

	this.metaItemCheckboxes = [
		{
			metaName: 'mwNoEditSection',
			label: ve.msg( 'visualeditor-dialog-meta-settings-noeditsection-label' )
		}
	].concat( ve.ui.MWSettingsPage.static.extraMetaCheckboxes );
	/*global mw*/
	if ( mw.config.get( 'wgNamespaceNumber' ) === mw.config.get( 'wgNamespaceIds' ).category ) {
		this.metaItemCheckboxes.push(
			{
				metaName: 'mwHiddenCategory',
				label: ve.msg( 'visualeditor-dialog-meta-settings-hiddencat-label' )
			},
			{
				metaName: 'mwNoGallery',
				label: ve.msg( 'visualeditor-dialog-meta-settings-nogallery-label' )
			}
		);
	}

	this.settingsFieldset.addItems( [
		this.enableRedirectField,
		this.redirectTargetField,
		this.tableOfContents
	] );

	$.each( this.metaItemCheckboxes, function () {
		this.fieldLayout = new OO.ui.FieldLayout(
			new OO.ui.CheckboxInputWidget( { '$': settingsPage.$ } ),
			{
				'$': settingsPage.$,
				'align': 'inline',
				'label': this.label
			}
		);
		settingsPage.settingsFieldset.addItems( [ this.fieldLayout ] );
	} );

	this.$element.append( this.settingsFieldset.$element );
};

/* Inheritance */

OO.inheritClass( ve.ui.MWSettingsPage, OO.ui.PageLayout );

/* Allow extra meta item checkboxes to be added by extensions etc. */
ve.ui.MWSettingsPage.static.extraMetaCheckboxes = [];

/**
 * Add a checkbox to the list of changeable page settings
 * @param {string} metaName The name of the DM meta item
 * @param {string} label The label to show next to the checkbox
 */
ve.ui.MWSettingsPage.static.addMetaCheckbox = function ( metaName, label ) {
	this.extraMetaCheckboxes.push( { 'metaName': metaName, 'label': label } );
};

/* Methods */

/* Table of Contents methods */

/**
 * @inheritdoc
 */
ve.ui.MWSettingsPage.prototype.setOutlineItem = function ( outlineItem ) {
	// Parent method
	OO.ui.PageLayout.prototype.setOutlineItem.call( this, outlineItem );

	if ( this.outlineItem ) {
		this.outlineItem
			.setIcon( 'settings' )
			.setLabel( ve.msg( 'visualeditor-dialog-meta-settings-section' ) );
	}
};

/**
 * Handle Table Of Contents display change events.
 *
 * @method
 */
ve.ui.MWSettingsPage.prototype.onTableOfContentsFieldChange = function () {
	this.tableOfContentsTouched = true;
};

/* Redirect methods */

/**
 * Handle redirect state change events.
 *
 * @param {boolean} value Whether a redirect is to be set for this page
 */
ve.ui.MWSettingsPage.prototype.onEnableRedirectChange = function ( value ) {
	this.redirectTargetInput.setDisabled( !value );
	if ( !value ) {
		this.redirectTargetInput.setValue( '' );
	}
	this.redirectOptionsTouched = true;
};

/**
 * Handle redirect target change events.
 */
ve.ui.MWSettingsPage.prototype.onRedirectTargetChange = function () {
	this.redirectOptionsTouched = true;
};

/**
 * Get the first meta item of a given name
 *
 * @param {string} name Name of the meta item
 * @returns {Object|null} Meta item, if any
 */
ve.ui.MWSettingsPage.prototype.getMetaItem = function ( name ) {
	return this.metaList.getItemsInGroup( name )[0] || null;
};

/**
 * Setup settings page.
 *
 * @param {ve.dm.MetaList} metaList Meta list
 * @param {Object} [data] Dialog setup data
 */
ve.ui.MWSettingsPage.prototype.setup = function ( metaList ) {
	this.metaList = metaList;

	var // Table of Contents items
		tableOfContentsMetaItem = this.getMetaItem( 'mwTOC' ),
		tableOfContentsField = this.tableOfContents.getField(),
		tableOfContentsMode = tableOfContentsMetaItem &&
			tableOfContentsMetaItem.getType() || 'default',

		// Redirect items
		redirectTargetItem = this.getMetaItem( 'mwRedirect' ),
		redirectTarget = redirectTargetItem && redirectTargetItem.getAttribute( 'title' ) || '',

		settingsPage = this;

	// Table of Contents items
	tableOfContentsField.selectItem( tableOfContentsField.getItemFromData( tableOfContentsMode ) );
	this.tableOfContentsTouched = false;

	// Redirect items (disabled states set by change event)
	this.enableRedirectInput.setValue( !!redirectTargetItem );
	this.redirectTargetInput.setValue( redirectTarget );
	this.redirectTargetInput.setDisabled( !redirectTargetItem );
	this.redirectOptionsTouched = false;

	// Simple checkbox items
	$.each( this.metaItemCheckboxes, function () {
		var currentValue = !!settingsPage.getMetaItem( this.metaName );
		this.fieldLayout.getField().setValue( currentValue );
	} );
};

/**
 * Tear down settings page.
 *
 * @param {Object} [data] Dialog tear down data
 */
ve.ui.MWSettingsPage.prototype.teardown = function ( data ) {
	// Data initialisation
	data = data || {};

	var // Table of Contents items
		tableOfContentsMetaItem = this.getMetaItem( 'mwTOC' ),
		tableOfContentsSelectedItem = this.tableOfContents.getField().getSelectedItem(),
		tableOfContentsValue = tableOfContentsSelectedItem && tableOfContentsSelectedItem.getData(),

		// Redirect items
		currentRedirectTargetItem = this.getMetaItem( 'mwRedirect' ),
		newRedirectData = this.redirectTargetInput.getValue(),
		newRedirectItemData = { 'type': 'mwRedirect', 'attributes': { 'title': newRedirectData } },

		settingsPage = this;

	// Alter the TOC option flag iff it's been touched & is actually different
	if ( this.tableOfContentsTouched ) {
		if ( tableOfContentsValue === 'default' ) {
			if ( tableOfContentsMetaItem ) {
				tableOfContentsMetaItem.remove();
			}
		} else {
			if ( !tableOfContentsMetaItem ) {
				this.metaList.insertMeta( { 'type': tableOfContentsValue } );
			} else if ( tableOfContentsMetaItem.getType() !== tableOfContentsValue ) {
				tableOfContentsMetaItem.replaceWith(
					ve.extendObject( true, {},
						tableOfContentsMetaItem.getElement(),
						{ 'type': tableOfContentsValue }
					)
				);
			}
		}
	}

	// Alter the redirect options iff they've been touched & are different
	if ( this.redirectOptionsTouched ) {
		if ( currentRedirectTargetItem ) {
			if ( newRedirectData ) {
				if ( currentRedirectTargetItem.getAttribute( 'title' ) !== newRedirectData ) {
					// There was a redirect and is a new one, but they differ, so replace
					currentRedirectTargetItem.replaceWith(
						ve.extendObject( true, {},
							currentRedirectTargetItem.getElement(),
							newRedirectItemData
					) );
				}
			} else {
				// There was a redirect and is no new one, so remove
				currentRedirectTargetItem.remove();
			}
		} else {
			if ( newRedirectData ) {
				// There's no existing redirect but there is a new one, so create
				// HACK: Putting this at index 0, offset 0 so that it works – bug 61862
				this.metaList.insertMeta( newRedirectItemData, 0, 0 );
			}
		}
	}

	$.each( this.metaItemCheckboxes, function () {
		var currentItem = settingsPage.getMetaItem( this.metaName ),
			newValue = this.fieldLayout.getField().getValue();

		if ( currentItem && !newValue ) {
			currentItem.remove();
		} else if ( !currentItem && newValue ) {
			settingsPage.metaList.insertMeta( { 'type': this.metaName } );
		}
	} );

	this.metaList = null;
};
