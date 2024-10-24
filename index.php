<?php declare(strict_types=1);

use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Renderer\RendererConstant;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;

$autoloader = require_once './vendor/autoload.php';

function profile_differ_function($name, callable $callback) {
  $start = microtime(true);
  $output = $callback();
  $end = microtime(true);
  $duration = $end - $start;

  print "Method: $name\n";
  print "Duration: $duration\n";
  printf("Output size: %s\n", strlen($output)) ;
  print "==============================\n";
  file_put_contents(__DIR__ . "/output/$name.txt", $output);
}

function diff_php_sebastianbergmann() {
  $source = __DIR__ . '/assets/a.yml';
  $target = __DIR__ . '/assets/b.yml';
  // Should ideally use "stat --format %y b.yml"
  $date_format = 'Y-m-d H:i:s.u O';

  $differ = new Differ(new StrictUnifiedDiffOutputBuilder([
    'fromFile' => $source,
    'fromFileDate' => (\DateTimeImmutable::createFromFormat('U', '' . filemtime($source)))->format($date_format),
    'toFile' => $target,
    'toFileDate' => (\DateTimeImmutable::createFromFormat('U', '' . filemtime($target)))->format($date_format),
  ]));
  $a = file_get_contents($source);
  $b = file_get_contents($target);
  return $differ->diff($a, $b);
}

function diff_php_jfcherng() {
  $source = __DIR__ . '/assets/a.yml';
  $target = __DIR__ . '/assets/b.yml';
  // Should ideally use "stat --format %y b.yml"
  $date_format = 'Y-m-d H:i:s.u O';

  $result = sprintf("--- %s\t%s\n", realpath($source), (\DateTimeImmutable::createFromFormat('U', '' . filemtime($source)))->format($date_format));
  $result .= sprintf("+++ %s\t%s\n", realpath($target), (\DateTimeImmutable::createFromFormat('U', '' . filemtime($source)))->format($date_format));
  $result .= DiffHelper::calculateFiles($source, $target, 'Unified', [], [
    'cliColorization' => RendererConstant::CLI_COLOR_DISABLE,
  ]);
  return $result;
}

function diff_native() {
  $diff_executable = shell_exec('command -v diff 2> /dev/null');
  if (!$diff_executable) {
    return '';
  }
  $diff_executable = trim($diff_executable);
  if (is_executable($diff_executable)) {
    $diff_cmd = sprintf(
      '%s -u %s %s',
      escapeshellcmd($diff_executable),
      escapeshellarg(__DIR__ . '/assets/a.yml'),
      escapeshellarg(__DIR__ . '/assets/b.yml')
    );
    return shell_exec($diff_cmd);
  }
  return '';
}

function diff_php_xdiff_extension() {
  $source = __DIR__ . '/assets/a.yml';
  $target = __DIR__ . '/assets/b.yml';
  $a = file_get_contents($source);
  $b = file_get_contents($target);

  return xdiff_string_diff($a, $b);
}

profile_differ_function('shell', 'diff_native');
if (extension_loaded('xdiff')) {
  // xdiff extension doesn't yet exist.
  profile_differ_function('php-php_xdiff', 'diff_php_xdiff_extension');
}
profile_differ_function('php-jfcherng', 'diff_php_jfcherng');
profile_differ_function('php-sebastianbergmann', 'diff_php_sebastianbergmann');