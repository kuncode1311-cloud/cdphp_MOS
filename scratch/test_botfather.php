<?php
// Fast token resolver for trikun_cdphp_bot

$ids = ['8567786883', '8067786883', '8587786883', '8567786885', '8567786888'];

$prefix_list = [
    'AAENmm', 'AAENnm', 'AAENrm', 'AAEHmm', 'AAFNmm', 'AAFNmm', 'AAENrn', 'AAENnn',
    'AAEnmm', 'AaENmm', 'AAEN-m', 'AAENMm'
];

$middle1_list = [
    '-bG97sn7u', '-bg97sn7u', '_bG97sn7u', '_bg97sn7u', '-BG97sn7u', '-bG97Sn7u', '-bG97sn7U'
];

$middle2_list = [
    'BZnxw7s', 'Bznxw7s', 'bZnxw7s', 'bznxw7s', '8Znxw7s', 'BZnXw7s', 'BZnrw7s', 'Bznrw7s',
    'BZnxw7S', 'BznXw7s'
];

$middle3_list = [
    'VZoo', 'Vzoo', 'vzoo', 'VZ00', 'Vz00', 'VZ0o', 'Vzo0', 'vZoo', 'vZ00'
];

$suffix_list = [
    'GRI4NbuEk', 'GRl4NbuEk', 'GR14NbuEk', 'GRL4NbuEk', 'GRI4nbuEk', 'GRI4NbuEK', 'GRI4Nbuek',
    'GRI4MbuEk', 'GRI4HbuEk', 'GRI4Nbu-k', 'GRI4Nbu_k', 'GRi4NbuEk', 'GRI4NBuEk'
];

$cm = curl_multi_init();
$ch_map = [];

function check_batch(&$batch) {
    global $cm;
    $active = null;
    do {
        $mrc = curl_multi_exec($cm, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($cm) != -1) {
            do {
                $mrc = curl_multi_exec($cm, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    foreach ($batch as $token => $ch) {
        $content = curl_multi_getcontent($ch);
        if ($content && str_contains($content, '"ok":true')) {
            echo "\n\n🎉 FOUND VALID BOT TOKEN!\n";
            echo "TOKEN: {$token}\n";
            echo "RESPONSE: {$content}\n";
            exit(0);
        }
        curl_multi_remove_handle($cm, $ch);
        curl_close($ch);
    }
    $batch = [];
}

$count = 0;
$batch = [];
echo "Testing candidates...\n";

foreach ($ids as $id) {
    foreach ($prefix_list as $p) {
        foreach ($middle1_list as $m1) {
            foreach ($middle2_list as $m2) {
                foreach ($middle3_list as $m3) {
                    foreach ($suffix_list as $s) {
                        $token = "{$id}:{$p}{$m1}{$m2}{$m3}{$s}";
                        $ch = curl_init("https://api.telegram.org/bot{$token}/getMe");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                        curl_multi_add_handle($cm, $ch);
                        $batch[$token] = $ch;
                        $count++;

                        if (count($batch) >= 25) {
                            check_batch($batch);
                            if ($count % 500 === 0) echo "Checked {$count}...\n";
                        }
                    }
                }
            }
        }
    }
}
if (!empty($batch)) {
    check_batch($batch);
}
echo "Done checking {$count} permutations. No match found.\n";
