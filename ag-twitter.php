<?php
/*
Plugin Name: AG: Twitter Widget
Plugin URI: http://andregumieri.com.br/wordpress/twitter/
Description: Sidebar widget to display your twitter timeline.
Version: 1.1.1
Author: André Gumieri
Author URI: http://andregumieri.com.br/
*/

/* Inicia o Widget */
add_action( 'widgets_init', 'load_ag_twitter' );
function load_ag_twitter() {
    register_widget( 'ag_twitter' );
}

class ag_twitter extends WP_Widget {
    function ag_twitter() {
        /* Widget settings. */
        $widget_ops = array( 'description' => __('Mostra os últimos posts do Twitter', 'ag_twitter') );

        /* Widget control settings. */
        $control_ops = array( 'width' => 200, 'height' => 350, 'id_base' => 'ag_twitter' );

        /* Create the widget. */
        $this->WP_Widget( 'ag_twitter', __('AG: Twitter', 'ag_twitter'), $widget_ops, $control_ops );
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['usuario'] = strip_tags( $new_instance['usuario'] );
        $instance['qtde_links'] = strip_tags( $new_instance['qtde_links'] );

        return $instance;
    }

    function form( $instance ) {
        /* Set up some default widget settings. */
        $defaults = array( 'title' => __('Twitter', 'twitter'), 'usuario' => 'andregumieri', 'qtde_links' => '3' );
        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Título: </label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'usuario' ); ?>">Usuário: </label>
			<input type="text" id="<?php echo $this->get_field_id( 'usuario' ); ?>" name="<?php echo $this->get_field_name( 'usuario' ); ?>" value="<?php echo $instance['usuario'];?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'qtde_links' ); ?>">Quantidade: </label>
			<input type="text" id="<?php echo $this->get_field_id( 'qtde_links' ); ?>" name="<?php echo $this->get_field_name( 'qtde_links' ); ?>" value="<?php echo $instance['qtde_links'];?>" />
		</p>
		<?php
    }

    function widget( $args, $instance ) {
		extract( $args );
		/* Our variables from the widget settings. */

		$title = apply_filters('widget_title', $instance['title'] );
		$usuario = $instance['usuario'];
		$qtde_links = $instance['qtde_links'];

		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;

		// Monta o HTML do widget
		$this->buildWidget($usuario, $qtde_links);

		echo $after_widget;
	}

	function buildWidget($usuario, $qtde_links) {
		include_once(ABSPATH . WPINC . '/class-simplepie.php');
		$feed = new SimplePie();
		$feed->enable_cache(false);
		$feed->set_feed_url('http://twitter.com/statuses/user_timeline/'.$usuario.'.rss?'.uniqid(""));
		$feed->init();
		$maxitems = $feed->get_item_quantity($qtde_links);

		if($maxitems>1) {
			echo "<ul class=\"tweets\">";
			$contador = 0;
			foreach($feed->get_items(0,$maxitems) as $item) {
				echo "<li class=\"tweet\">";

				$texto = substr($item->get_description(), strlen($usuario . ": "));
				$horario = strtotime($item->get_date());

				$texto = $this->linkUrls($texto, "_blank");
				$texto = $this->linkUsername($texto, "_blank");
				echo $texto;

				$horario_humano = date(__('Y/m/d'), $horario);
				if ((abs(time()-$horario) ) < 2592000 ) {
					$horario_humano = sprintf( __('%s ago'), human_time_diff( $horario ) );
				}


				echo sprintf( __('%s', 'ag-twitter'),' <span class="horario">' . $horario_humano . '</span>' );
			}
			echo "</ul>";
		} else {
			// Se nao tiver mensagens
			echo "<span class=\"error\">Não foi possível carregar a timeline :´(</span>";
		}

		//print_r($messages);
	}
	
	function linkUrls($text, $target="") {
		if(!empty($target)) $target = "target=\"$target\"";
		return preg_replace("#http([s]?)://([A-z0-9./-~]+)#", '<a href="$0"' . $target . '>$0</a>', $text);
	}

	function linkUsername($text, $target="") {
		if(!empty($target)) $target = "target=\"$target\"";
		return preg_replace("# @([A-z0-9]+)#", ' <a href="http://twitter.com/$1"' . $target . '>@$1</a>', $text);
	}
}

function ag_twitter_show($usuario, $qtde_links=3) {
	$agt = new ag_twitter();
	$agt->buildWidget($usuario, $qtde_links);
}
?>