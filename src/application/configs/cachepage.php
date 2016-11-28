<?php

return array(
    'resources' => array(
        'cachemanager' => array(
            'page' => array(
                'frontend' => array(
                    'name' => 'Page',
                    'options' => array(
                        'debug_header' => FALSE,
                        'caching' => TRUE,
                        'lifetime' => 10 * 60,
                        'regexps' => array(
                            '^/' => array('cache' => FALSE),
                            '^/$' => array('cache' => FALSE),
                            '^/registro/$' => array('cache' => FALSE)
                            /*'^/buscar/' => array('cache' => FALSE),
                            '^/search-cmp/' =>
                            array('cache' => TRUE,
                                'cache_with_post_variables' => TRUE,
                                'tags' => array('search')
                            ),
                            '^/[a-z0-9-]+\-(\d+)' => array('cache' => FALSE),*/
                        )
                    ),
                    'default_options' => array(
                        'cache_with_get_variables' => true,
                        'cache_with_post_variables' => true,
                        'cache_with_session_variables' => true,
                        'cache_with_files_variables' => true,
                        'cache_with_cookie_variables' => true,
                        'make_id_with_get_variables' => true,
                        'make_id_with_post_variables' => true,
                        'make_id_with_session_variables' => true,
                        'make_id_with_files_variables' => true,
                        'make_id_with_cookie_variables' => true,
                        'cache' => false
                    )
                ),
                'backend' => array(
                    'name' => 'File',
                    'options' => array(
                        'cache_dir' => APPLICATION_PATH . '/../cache/'
                    )
                )
            )
        )
    )
);