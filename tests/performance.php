<?php
/*

This test script requires PEAR/Benchmark to measure the script runtime.


*/
if ( function_exists( 'xdebug_start_code_coverage' ) )
    xdebug_start_code_coverage();

require_once 'Benchmark/Timer.php';

$timer=new Benchmark_Timer();
$timer->start();

require('../xajax_core/xajax.inc.php');
//require( '../xajax_core/xajaxAIO.inc.php' );

$timer->setMarker( 'xajax included' );

// -- testing session serialization for xajax object
//session_start();
//
//unset($_SESSION['xjxcore']);
//
//$xajax = null;
//
//if (false == isset($_SESSION['xjxcore']))
//{
//	$xajax = new xajax();
//
//	$_SESSION['xjxcore'] = $xajax;
//}
//else
//{
//	$xajax = $_SESSION['xjxcore'];
//}
// -- end testing

$xajax = new xajax();

$xajax->register(XAJAX_FUNCTION, 'argumentDecode');
$xajax->register(XAJAX_FUNCTION, 'roundTrip');
$xajax->register(XAJAX_FUNCTION, 'compress');
$xajax->register(XAJAX_FUNCTION, 'compile');

$timer->setMarker( 'xajax constructed' );

$trips=500;

$timer->setMarker( 'begin process request' );

$xajax->processRequest();
$xajax->configure('javascript URI','../');

$timer->setMarker( 'after process request' );

function argumentDecode( $nTimes, $aArgs )
{
    global $timer;
    global $trips;
    $objResponse=new xajaxResponse();

    if ( $nTimes < $trips )
    {
        $nTimes+=1;
        $objResponse->script( 'xajax_argumentDecode(' . $nTimes . ', jsArray);' );
        $objResponse->assign( 'submittedDiv', 'innerHTML', 'Working...' );
        $objResponse->append( 'submittedDiv', 'innerHTML', print_r( $aArgs, true ) );
    }
    else
    {
        $objResponse->assign( 'submittedDiv', 'innerHTML', 'Done' );
        ob_start();
        var_dump( xdebug_get_code_coverage());
        $objResponse->append( 'submittedDiv', 'innerHTML', ob_get_clean() );
    }
    $timer->stop();
    $objResponse->call( 'accumulateTime', $timer->timeElapsed() );
    $objResponse->call( 'printTime' );
    return $objResponse;
}

function roundTrip( $nTimes )
{
    global $timer;
    global $trips;
    $objResponse=new xajaxResponse();

    if ( $nTimes < $trips )
    {
        $nTimes+=1;
        $objResponse->script( 'xajax_roundTrip(' . $nTimes . ');' );
        $objResponse->assign( 'submittedDiv', 'innerHTML', 'Working...' );
    }
    else
    {
        $objResponse->assign( 'submittedDiv', 'innerHTML', 'Done' );
    }
    $timer->stop();
    $objResponse->call( 'accumulateTime', $timer->timeElapsed() );
    $objResponse->call( 'printTime' );
    return $objResponse;
}

function compress( )
{
    global $xajax;
    $xajax->_compressSelf();

    $objResponse=new xajaxResponse();
    $objResponse->assign( 'submittedDiv', 'innerHTML', 'Compressed' );
    return $objResponse;
}

function compile( )
{
    global $xajax;
    $xajax->_compile();

    $objResponse=new xajaxResponse();
    $objResponse->assign( 'submittedDiv', 'innerHTML', 'Compiled' );
    return $objResponse;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
    <head>
        <title>Performance Test</title>

        <?php $xajax->printJavascript( '../' ); ?>

        <script type = 'text/javascript'>
            nCumulativeTime = 0;

            nTrips = 0;

            accumulateTime = function(nTime)
                {
                nCumulativeTime += (nTime * 1);
                nTrips += 1;
                }

            printTime = function()
                {
                xajax.$('result').innerHTML = 'Trips: ' + nTrips +
                    '<br />Total time: ' + nCumulativeTime +
                    '<br />Average time: ' + nCumulativeTime / nTrips;
                }

            jsArray =
                {
                a: [1, 2, 3, 4, 5],
                b: [1, 2, 3, 4, 5, 6],
                c: [1, 2, 3, 4, 5, 6, 7],
                d: [1, 2, 3, 4, 5, 6, 7, 8],
                e: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                f: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                };
        </script>
    </head>

    <body>
        <h2><a href = "index.php">xajax Tests</a></h2>

        <h1>Redirect Test</h1>

        <form id = "testForm1" onsubmit = "return false;">
            <p>
                <input type = 'submit' value = 'Begin argumentDecode' name = 'begin' id = 'begin'
                    onclick = 'nCumulativeTime = 0; nTrips=0; xajax_argumentDecode(0, jsArray); return false;' />
            </p>

            <p>
                <input type = 'submit' value = 'Begin roundTrip' name = 'begin'
                    id = 'Submit1' onclick = 'nCumulativeTime = 0; nTrips=0; xajax_roundTrip(0); return false;' />
            </p>

            <p>
                <input type = 'submit'
                    value = 'Compress' name = 'compress' id = 'compress' onclick = 'xajax_compress(); return false;' />
            </p>

            <p>
                <input type = 'submit'
                    value = 'Compile' name = 'compile' id = 'compile' onclick = 'xajax_compile(); return false;' />
            </p>
        </form>

        <div id = "submittedDiv">
        </div>

        <div id = "result">
        </div>

        <?php
        $timer->stop();
        $timer->display();

        if ( function_exists( 'xdebug_get_code_coverage' ) )
            var_dump( xdebug_get_code_coverage());
        ?>
    </body>
</html>

