<?php
/**
 * Don't mess with the spacing/indentation!
 *
 * Note: Since any site can filter an email's content type before sending, it seems
 * that we can never be certain that a text email is actually being sent with a text
 * content type. With that in mind, all values are escaped under the assumption that
 * this message has been sent with an HTML content type.
 */

?>Vulnerabilities were detected on <?php echo esc_html( $site_name ) ?>. We've included some details to help you fix the problem.

[<?php echo esc_html( $site_name ) ?>] Security Digest

******************
<?php echo esc_html( $count ) ?> Vulnerabilities Detected!
******************

A recent scan by the Soter security check plugin flagged <?php echo esc_html( $count ) ?> vulnerabilities on your WordPress site.

Please ensure your WordPress install as well as all plugins and themes are up-to-date from your dashboard:

Go To Dashboard ( <?php echo esc_url( $action_url ) ?> )

For reference, here are the details of the flagged vulnerabilities:

<?php foreach ( $messages as $message ) : ?>
<?php // This feels so wrong... ?>
<?php echo esc_html( str_replace(
	[ '<', '>', '=' ],
	[ 'less than', 'greater than', ' or equal to' ],
	$message['title']
) ) ?>

<?php foreach ( $message['links'] as $url => $host ) : ?>
<?php echo esc_url( $url ) ?>

<?php endforeach ?>
<?php echo implode( ' | ', array_map( 'esc_html', $message['meta'] ) ) ?>


<?php endforeach ?>
