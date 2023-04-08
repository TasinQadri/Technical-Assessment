{ pkgs, config, ... }:

{
  # https://devenv.sh/basics/
  env.GREET = "devenv";

  # https://devenv.sh/packages/
  packages = [ pkgs.git ];

  # https://devenv.sh/scripts/
  enterShell = ''
      if [[ ! -d vendor ]]; then
          composer install
      fi

      if [[ ! -f .env ]]; then
          echo "API_URL='http://127.0.0.1:8000/api.php/'" > .env
      fi
  '';

  # https://devenv.sh/languages/
  # languages.nix.enable = true;

  languages.php = {
    enable = true;
    version = "8.1";
    ini = ''
      memory_limit = 256M
      variables_order = "EGPCS"
    '';
    fpm.pools.web = {
      settings = {
        "clear_env" = "no";
        "pm" = "dynamic";
        "pm.max_children" = 10;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 1;
        "pm.max_spare_servers" = 10;
      };
    };
  };

  # https://devenv.sh/services/
  services.caddy.enable = true;
  services.caddy.virtualHosts.":8000" = {
    extraConfig = ''
      root * public
      php_fastcgi unix/${config.languages.php.fpm.pools.web.socket}
      file_server
    '';
  };

  # https://devenv.sh/pre-commit-hooks/
  # pre-commit.hooks.shellcheck.enable = true;

  # https://devenv.sh/processes/
  # processes.ping.exec = "ping example.com";

  # See full reference at https://devenv.sh/reference/options/
}
