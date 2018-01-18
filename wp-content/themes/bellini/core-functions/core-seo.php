<?php
global $bellini;

if ( ! function_exists( 'bellini_structured_data' ) ) :

    function bellini_structured_data(){
        global $post;

        $site_name                  = get_bloginfo("name");
        $site_description           = get_bloginfo("description");
        $site_url                   = home_url();
        $inLanguage                 = get_bloginfo("language");
        $title 			            = esc_html($post->post_title);
        $commentCount               = intval($post->comment_count);
        $articleBody                = esc_html($post->post_content);
        $url 			            = esc_url(get_permalink( $post->ID ));
        $datePublished              = get_the_time('Y-m-d', $post->ID);
        $dateModified               = get_post_field('post_modified', $post->ID );
        $description                = esc_html(get_the_excerpt( $post->ID ));
        $author                     = esc_html(get_the_author_meta( 'display_name', $post->ID));
        $image_data                 = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "thumbnail" );


        if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            $custom_logo_id             = get_theme_mod( 'custom_logo' );
            $organization_logo          = wp_get_attachment_image_src( $custom_logo_id , 'full' );
        }

        // is_single()
        // BlogPosting
        if ( is_single() ){
          $BlogPosting = array(
            '@context'      		=> "http://schema.org",
            '@type'         		=> "BlogPosting",
            'headline'              => $title,
            'datePublished'         => $datePublished,
            'dateModified'          => $dateModified,
            'description'           => $description,
            'articleBody'           => $articleBody,
            'inLanguage'            => $inLanguage,
            'commentCount'          => $commentCount,
            'mainEntityOfPage'  	=> array(
                '@type'                 => "WebPage",
                '@id'                   => $url
            ),
            'image'         		=> array(
            	'@type'					=> "ImageObject",
            	'url'					=> $image_data[0],
            	'width'					=> array(
                    '@type'                  => "Intangible",
                    'name'                   => $image_data[1],
                ),
                'height'                 => array(
                    '@type'                  => "Intangible",
                    'name'                   => $image_data[2],
                ),
            ),
            'author'				=> array(
            	'@type'					=> "Person",
            	'name'					=> $author
            ),
            'publisher'				=> array(
            	'@type'					=> "Organization",
            	'name'					=> $site_name,
                'logo'				    => array(
            	    	'@type'					=> "ImageObject",
            	    	'url'					=> $organization_logo[0],
                        'width'                 => array(
                            '@type'                  => "Intangible",
                            'name'                   => $organization_logo[1],
                        ),
                        'height'                 => array(
                            '@type'                  => "Intangible",
                            'name'                   => $organization_logo[2],
                        ),
            	)
            )
          );

          echo '<script type="application/ld+json">' . json_encode($BlogPosting, JSON_UNESCAPED_SLASHES) . '</script>';
        }


        if( is_page() ){
          $WebsiteSchema = array(
            '@context'              => "http://schema.org",
            '@type'                 => "WebSite",
            'url'                   => $site_url,
            'name'                  => $site_name,
            'description'           => $site_description,
          );

          echo '<script type="application/ld+json">' . json_encode($WebsiteSchema, JSON_UNESCAPED_SLASHES) . '</script>';
        }

        if( is_page() ){
          $WebpageSchema = array(
            '@context'              => "http://schema.org",
            '@type'                 => "WebPage",
            'url'                   => $url,
            'name'                  => $title,
          );

          echo '<script type="application/ld+json">' . json_encode($WebpageSchema, JSON_UNESCAPED_SLASHES) . '</script>';
        }

    }
endif;