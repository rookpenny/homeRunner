<?php
/**
 * Hostaway Listings Widget
 *
 * Allows listings to be displayed in any widgetized area
 */
class Hostaway_Listings_Widget extends WP_Widget {

    /**
     * Register widget
     */
    public function __construct() {
        parent::__construct(
            'hostaway_listings_widget',
            'Hostaway Listings',
            array(
                'description' => 'Display Hostaway property listings',
                'classname'   => 'hostaway-listings-widget',
            )
        );
    }

    /**
     * Front-end display of widget
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        // Title
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        // Build shortcode
        $atts = array();
        if (!empty($instance['limit'])) {
            $atts[] = 'limit="' . intval($instance['limit']) . '"';
        }
        if (!empty($instance['columns'])) {
            $atts[] = 'columns="' . intval($instance['columns']) . '"';
        }
        if (!empty($instance['property_type'])) {
            $atts[] = 'property_type="' . esc_attr($instance['property_type']) . '"';
        }
        if (!empty($instance['location'])) {
            $atts[] = 'location="' . esc_attr($instance['location']) . '"';
        }
        if (!empty($instance['orderby'])) {
            $atts[] = 'orderby="' . esc_attr($instance['orderby']) . '"';
        }

        $shortcode = '[hostaway_listings ' . implode(' ', $atts) . ']';

        // Display listings
        echo do_shortcode($shortcode);

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Featured Properties';
        $limit = !empty($instance['limit']) ? $instance['limit'] : '3';
        $columns = !empty($instance['columns']) ? $instance['columns'] : '3';
        $property_type = !empty($instance['property_type']) ? $instance['property_type'] : '';
        $location = !empty($instance['location']) ? $instance['location'] : '';
        $orderby = !empty($instance['orderby']) ? $instance['orderby'] : 'date';
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">Number of Listings:</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('limit'); ?>"
                   name="<?php echo $this->get_field_name('limit'); ?>"
                   type="number"
                   value="<?php echo esc_attr($limit); ?>"
                   min="1"
                   max="50">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('columns'); ?>">Columns:</label>
            <select class="widefat"
                    id="<?php echo $this->get_field_id('columns'); ?>"
                    name="<?php echo $this->get_field_name('columns'); ?>">
                <option value="1" <?php selected($columns, '1'); ?>>1 Column</option>
                <option value="2" <?php selected($columns, '2'); ?>>2 Columns</option>
                <option value="3" <?php selected($columns, '3'); ?>>3 Columns</option>
                <option value="4" <?php selected($columns, '4'); ?>>4 Columns</option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('property_type'); ?>">Property Type (slug):</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('property_type'); ?>"
                   name="<?php echo $this->get_field_name('property_type'); ?>"
                   type="text"
                   value="<?php echo esc_attr($property_type); ?>"
                   placeholder="e.g., apartment">
            <small>Leave blank for all types</small>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('location'); ?>">Location (slug):</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('location'); ?>"
                   name="<?php echo $this->get_field_name('location'); ?>"
                   type="text"
                   value="<?php echo esc_attr($location); ?>"
                   placeholder="e.g., miami">
            <small>Leave blank for all locations</small>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('orderby'); ?>">Sort By:</label>
            <select class="widefat"
                    id="<?php echo $this->get_field_id('orderby'); ?>"
                    name="<?php echo $this->get_field_name('orderby'); ?>">
                <option value="date" <?php selected($orderby, 'date'); ?>>Date</option>
                <option value="title" <?php selected($orderby, 'title'); ?>>Title</option>
                <option value="rand" <?php selected($orderby, 'rand'); ?>>Random</option>
            </select>
        </p>

        <?php
    }

    /**
     * Sanitize widget form values
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? absint($new_instance['limit']) : 3;
        $instance['columns'] = (!empty($new_instance['columns'])) ? absint($new_instance['columns']) : 3;
        $instance['property_type'] = (!empty($new_instance['property_type'])) ? sanitize_text_field($new_instance['property_type']) : '';
        $instance['location'] = (!empty($new_instance['location'])) ? sanitize_text_field($new_instance['location']) : '';
        $instance['orderby'] = (!empty($new_instance['orderby'])) ? sanitize_text_field($new_instance['orderby']) : 'date';

        return $instance;
    }
}

/**
 * Register the widget
 */
function hostaway_register_widgets() {
    register_widget('Hostaway_Listings_Widget');
}
add_action('widgets_init', 'hostaway_register_widgets');
