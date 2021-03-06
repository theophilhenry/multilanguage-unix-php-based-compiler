<?php
$result = "Accepted";
$runtime = 0;

//proses
$lang = "cpp";
$command1 = "clang++ submission/cpp.cpp -o temp/compiled_cpp"; // for compile
$command2 = "time temp/compiled_cpp <soal/cpp.in> temp/result_cpp.out";

$descriptorspec = array(
    0 => array("pipe", "r"), // stdin is a pipe that the child will read from
    1 => array("pipe", "w"), // stdout is a pipe that the child will write to
    2 => array("pipe", "w") //stderr is a pipe that the child will write to
);

$process = proc_open($command1, $descriptorspec, $pipes);


if (is_resource($process)) {
    $out = stream_get_contents($pipes[1]);
    echo "1." . $out . "<br />";
    fclose($pipes[1]);

    $out = stream_get_contents($pipes[2]);
    echo "2." . $out . "<br />";
    fclose($pipes[2]);

    if (proc_close($process) != 0) $result = 'Compile Error';
}


//Check time limit
if ($result == "Accepted") {
    $memory_limit = 64 * 1024; //64MB
    $time_limit = 30; //15second

    $process = proc_open("bash -c 'ulimit -St $time_limit -Sm $memory_limit ; $command2'", $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        $stream = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $return_value = proc_close($process);

        $timelimitstring = "CPU time limit exceeded";
        $memorylimitstring = "Memory size limit exceeded";

        if (strstr($stream, $timelimitstring) != null) {
            $result = 'Time Limit Exceeded';
        }

        if (strstr($stream, $memorylimitstring) != null) {
            $result = "Memory Limit Exceeded";
        }

        if ($result == "Accepted" && substr($stream, 1, 4) != "real") {
            $result = "Run Time Error";
        }

        $str = strstr($stream, "real");
        $str = str_replace(",", ".", $str);
        $im = strpos($str, "m");
        $is = strpos($str, "s");
        $m = substr($str, 5, $im - 5);
        $s = substr($str, $im + 1, $is - $im - 1);
        $runtime = number_format($m * 60 + $s, 3);
    }
}

//jika tetap masih AC(uda lewat TL), harus dicek sama tidak dengan output yang diinginkan
if ($result == "Accepted") {
    $process = proc_open('cmp temp/result_cpp.out soal/cpp.out', $descriptorspec, $pipes);

    if (is_resource($process)) {
        $return_value = proc_close($process);
        if ($return_value != 0)
            $result = "Wrong Answer";
    }
}

echo "result : " . $result . "<br />";
echo "runtime : " . $runtime . "<br />";

// shell_exec('rm -r temp/*');
