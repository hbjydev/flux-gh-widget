<?php
/*
Plugin Name: FluX GitHub Widget
Plugin URI: https://wordpress.org/extend/plugins/#
Description: A plugin to pull information from GitHub
Author: Hayden Young
Version: 18.12.2
Author URI: https://haydennyyy.com
*/

require_once __DIR__ . '/vendor/autoload.php';

add_action( 'widgets_init', function() {
  register_widget( 'FluX_GitHub_Widget' );
});

class FluX_GitHub_Widget extends WP_Widget {
  // Class Constructor
  public function __construct() {
    $widget_meta = array(
      'classname' => 'flux_github_widget',
      'description' => 'A plugin to pull information from GitHub'
    );
    parent::__construct( 'flux_github_widget', 'FluX_GitHub_Widget', $widget_meta );
  }

  // Output widget content
  public function widget( $args, $instance ) {
    if(false === ($repos = get_transient('flux_gh_widget_' . $instance['username']))) {
      $ghClient = new \Github\Client();
      $repos = $ghClient->api('user')->repositories( $instance['username'] );
      set_transient('flux_gh_widget_' . $instance['username'], $repos, HOUR_IN_SECONDS);
    }

    echo $args['before_widget'];
    if ( !empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters('widget_title', $instance['title'] ) . $args['after_title'];
    }


    if ( !empty( $repos ) && is_array( $repos ) ) {
      foreach ( $repos as $key => $node ) {
        $timestamps[$key] = $node['updated_at'];
      }
      array_multisort( $timestamps, SORT_DESC, $repos );

      foreach ( $repos as $key => $repo ) {
        if($key < 5) {
          print_r( '<h3><a href="' . $repo['html_url'] . '">' . $repo['name'] . "</a></h3>" );
        }
      }
    }
    echo $args['after_widget'];
  }

  // Output options form
  public function form( $instance ) {
    $title = !empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Title', 'flux-gh-widget' );
    $username = !empty( $instance['username'] ) ? $instance['username'] : esc_html__( 'Username', 'flux-gh-widget' ); ?>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
        <?php esc_attr_e( 'Title:', 'flux-gh-widget' ); ?>
      </label>

      <input class="widefat"
        id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
        name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
        type="text"
        value="<?php echo esc_attr( $title ); ?>" />

      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
        <?php esc_attr_e( 'GitHub Username:', 'flux-gh-widget' ); ?>
      </label>

      <input class="widefat"
        id="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>"
        name="<?php echo esc_attr( $this->get_field_name( 'username' ) ); ?>"
        type="text"
        value="<?php echo esc_attr( $username ); ?>" />
    </p>
    <?php
  }

  // Save options
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['username'] = ( ! empty( $new_instance['username'] ) ) ? strip_tags( $new_instance['username'] ) : 'haydennyyy';

    delete_transient('flux_gh_widget_' . $new_instance['username']);

    return $instance;
  }
}