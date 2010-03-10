<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_tendflickr_photo'] = array (
	'ctrl' => $TCA['tx_tendflickr_photo']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'title,description,author,photo,flickr_meta,upload_timestamp'
	),
	'feInterface' => $TCA['tx_tendflickr_photo']['feInterface'],
	'columns' => array (
		'title' => array (		
			'exclude' => 0,		
			'label' => 'Title',
			'config' => array (
				'type' => 'input',	
				'size' => '40',	
				'eval' => 'required,trim',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'Description',
			'config' => array (
				'type' => 'text',
				'cols' => '40',	
				'rows' => '5',
			)
		),
		'author' => array (		
			'exclude' => 0,		
			'label' => 'Author',
			'config' => array (
				'type' => 'input',	
				'size' => '40',	
				'eval' => 'trim',
			)
		),
		'photo' => array (		
			'exclude' => 0,		
			'label' => 'Photo',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_tendflickr',
				'show_thumbs' => 1,	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'flickr_meta' => array (		
			'exclude' => 0,		
			'label' => 'Flickr Metadata',
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			)
		),
		'upload_timestamp' => array (		
			'exclude' => 0,		
			'label' => 'Upload timestamp',
			'config' => array (
				'type'     => 'input',
				'size'     => '12',
				'max'      => '20',
				'eval'     => 'datetime',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'title;;;;2-2-2, description;;;;3-3-3, author, photo, flickr_meta, upload_timestamp')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>