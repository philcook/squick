<?php
/**
 * Squick WordPress helpers.
 */
class Squick_Command extends WP_CLI_Command {


    /**
    * Squickly installs WordPress using config from wp-cli.local.yml and wp-cli.yml
    *
    * EXAMPLE
    *
    *     wp squick install
    *
    * @when before_wp_load
    */
    public function install($args, $assoc_args) {
        // rescue directory
        $rescue_directory = getcwd();

        // Get config
        $config = \WP_CLI::get_runner()->config;

        // get extra config
        $extra_config = \WP_CLI::get_runner()->extra_config;

        // 1) download ...
        WP_CLI::run_command( array('core', 'download'));

        // 2) create database ...
        $db_config = $extra_config['core config'];
        if(empty($db_config['dbhost'])) {
            $db_config['dbhost'] = '127.0.0.1';
        }
        $mysqli = new mysqli($db_config['dbhost'], $db_config['dbuser'], $db_config['dbpass']);
        if( ! $mysqli) {
            WP_CLI::error(sprintf("Unable to connect to database '%s' with user '%s'.", $db_config['dbhost'], $db_config['dbuser']));
            return;
        }
        if( ! $mysqli->query("CREATE DATABASE IF NOT EXISTS `" . $db_config['dbname']. "` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;")) {
            WP_CLI::error(sprintf("Unable to create new schema '%s'.", $db_config['dbname']));
            return;
        }
        $mysqli->close();
        WP_CLI::success(sprintf("Created '%s' database.", $db_config['dbname']));

        // 3) config ...
        WP_CLI::run_command(array('core', 'config'));

        // 4) install ..
        WP_CLI::run_command(array('core', 'install'));
        WP_CLI::success("Finished core install.");

        // 5) plugin install
        WP_CLI::line('Installing list of plugins...');
        foreach ($extra_config['plugin install'] as $plugin) {
            WP_CLI::run_command( array('plugin', 'install' , $plugin));
            WP_CLI::run_command( array('plugin', 'activate' , $plugin));
            WP_CLI::success(sprintf("Finished installing %s plugin",$plugin));
        }

        // rescue directory
        chdir($rescue_directory);

        if(isset($config['url'])) {
            WP_CLI::launch('open ' . $config['url']);
        }
    }

}

WP_CLI::add_command( 'squick', 'Squick_Command' );
