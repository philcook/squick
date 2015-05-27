<?php
/**
 * Squick WordPress helpers.
 */

class SquickCommand extends WP_CLI_Command
{
    /**
    * Squickly installs WordPress using config from wp-cli.local.yml and wp-cli.yml
    *
    * EXAMPLE
    *
    *     wp squick install
    *
    * @when before_wp_load
    */
    public function install($args, $assoc_args)
    {
        // rescue directory
        $rescue_directory = getcwd();

        // Get config
        $config = \WP_CLI::get_runner()->config;

        // get extra config
        $extra_config = \WP_CLI::get_runner()->extra_config;

        // 1) download ...
        WP_CLI::run_command(array('core', 'download'));

        // 2) create database ...
        $db_config = $extra_config['core config'];
        if (empty($db_config['dbhost'])) {
            $db_config['dbhost'] = '127.0.0.1';
        }
        $mysqli = new mysqli($db_config['dbhost'], $db_config['dbuser'], $db_config['dbpass']);
        if (! $mysqli) {
            WP_CLI::error(
                sprintf(
                    "Unable to connect to database '%s' with user '%s'.",
                    $db_config['dbhost'],
                    $db_config['dbuser']
                )
            );
            return;
        }

        $sql = "CREATE DATABASE IF NOT EXISTS `".$db_config['dbname'].
            "` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;";
        if (! $mysqli->query($sql)) {
            WP_CLI::error(sprintf("Unable to create new schema '%s'.", $db_config['dbname']));
            return;
        }
        $mysqli->close();
        WP_CLI::success(sprintf("Created '%s' database.", $db_config['dbname']));

        // 3) config ...
        WP_CLI::run_command(array('core', 'config'));

        // 4) install ..
        echo exec('wp core install')."\n";

        // 5) Remove default plugins and themes
        WP_CLI::line('Deleting list of skipped plugins...');
        foreach ($config['skip-plugins'] as $plugin) {
            echo exec('wp plugin delete '.$plugin)."\n";
        }

        // 6) plugin install
        WP_CLI::line('Installing list of plugins...');
        foreach ($extra_config['plugin install'] as $plugin) {
            echo exec('wp plugin install '.$plugin. ' --activate')."\n";
        }

        // 7) Activate theme and delete others
        WP_CLI::line('Activating theme name...');
        echo exec('wp theme activate '.$extra_config['theme-name'])."\n";

        WP_CLI::line('Deleting list of skipped themes...');
        foreach ($config['skip-themes'] as $theme) {
            echo exec('wp theme delete '.$theme)."\n";
        }

        chdir($rescue_directory);

        if (isset($config['url'])) {
            WP_CLI::launch('open ' . $config['url']);
        }
    }
}

WP_CLI::add_command('squick', 'SquickCommand');
