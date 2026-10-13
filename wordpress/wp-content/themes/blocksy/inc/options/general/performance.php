<?php
/**
 * Performance options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

/**
 * Filters the options prepended to the Performance customizer section.
 *
 * @since 1.5.7
 *
 * @param array $performance_start_options Options. Default empty array.
 */
$performance_start_options = apply_filters(
	'blocksy_performance_end_customizer_options',
	[]
);

/**
 * Filters the options added after the emojis option in the Performance customizer section.
 *
 * @since 2.0.1
 *
 * @param array $performance_after_emojis_options Options. Default empty array.
 */
$performance_after_emojis_options = apply_filters(
	'blocksy_performance_after_emojis_customizer_options',
	[]
);

$options = [
	'performance_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => [
			$performance_start_options,

			[
				[
					'emoji_scripts' => [
						'label' => __( 'Disable Emojis Script', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'yes',
						'setting' => [ 'transport' => 'postMessage' ],
						'desc' => __( 'Enable this option if you want to remove WordPress emojis script in order to improve the performance.', 'blocksy' )
					],
	
					blocksy_rand_md5() => [
						'type' => 'ct-divider'
					]
				],

				$performance_after_emojis_options,

				'has_lazy_load' => [
					'label' => __( 'Lazy Load Images', 'blocksy' ),
					'type' => 'ct-switch',
					'value' => 'yes',
					'setting' => [ 'transport' => 'postMessage' ],
					'desc' => __( 'Automatically lazy load images when appropriate.', 'blocksy' ),
				],
			],
		],
	],
];