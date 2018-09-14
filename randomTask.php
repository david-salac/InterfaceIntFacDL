<?php
//Class for arithmetic operations for large numbers (2^64 and bigger):
require_once './BigInteger.php';

/**
 * Generating random tasks for system (task type: ElGamal, RSA, Diffie-Hellman)
 * @author David Salac
 */
class RandomTaskGenerator {
    //Required modulus size in bits
    private $sizeType;
    //Task type (RSA, ElGamal or Diffie-Hellman)
    private $type;
    
    /**
     * Finds group generator (g) and order of group (p)
     * @return array First element of array is order of group (p) and the second is group generator (g)
     */
    private function findGandPPairEngine() : array {
        //GENERATING OF CYCLIC GROUP
        $rand = new Math_BigInteger();

        $bi0 = new Math_BigInteger("0");
        $bi1 = new Math_BigInteger("1");
        $bi2 = new Math_BigInteger("2");

        $i14 = new Math_BigInteger("16384");
        $i15 = new Math_BigInteger("32768");
        $i16 = new Math_BigInteger("65535");

        $i30 = new Math_BigInteger("1073741824");
        $i31 = new Math_BigInteger("2147483648");
        $i32 = new Math_BigInteger("4294967295");
        
        $i46 = new Math_BigInteger("70368744177664");
        $i47 = new Math_BigInteger("140737488355328");
        $i48 = new Math_BigInteger("281474976710656");

        $i62 = new Math_BigInteger("4611686018427387904");
        $i63 = new Math_BigInteger("9223372036854775808");
        $i64 = new Math_BigInteger("18446744073709551615");


        $p = $rand->randomPrime($i15, $i16);
        $g = $rand->randomPrime($i14, $i15);
        if($this->sizeType == 32) {
            $p = $rand->randomPrime($i31, $i32);
            $g = $rand->randomPrime($i30, $i31);
        } else if($this->sizeType == 48) {
            $p = $rand->randomPrime($i47, $i48);
            $g = $rand->randomPrime($i46, $i47);
        } else if($this->sizeType == 64) {
            $p = $rand->randomPrime($i63, $i64);
            $g = $rand->randomPrime($i62, $i63);
        }


        $phiP = $p->subtract($bi1);

        $phiPTemp = $p->subtract($bi1);
        $i = new Math_BigInteger("1");
        $upLimit = new Math_BigInteger("100000");
        $phiPFactors = array();
        for($i = new Math_BigInteger("2"); $i->compare($upLimit) < 0; $i = $i->add($bi1)) {
            $comp = $phiPTemp->divide($i)[1];
            if($comp->compare($bi0) == 0) {
                for($k = 0; $k < 256; $k++) {
                    $phiPNew = $phiPTemp->divide($i);
                    $phiPNewDiv = $phiPNew[0];
                    $phiPNewRem = $phiPNew[1];
                    if($phiPNewRem->compare($bi0) == 0) { $phiPTemp = $phiPNewDiv; }
                    else { break; }
                }

                $phiPFactors[] = $i;
                if($phiPTemp->isPrime()) { $phiPFactors[] = $phiPTemp; break; }
            }
        }
        if(!$phiPTemp->isPrime()) { 
            return array();
        }

        foreach ($phiPFactors as $factor) {
            $expL = $phiP->divide($factor);
            $rem = $expL[1];
            $exp = $expL[0];
            if($rem->compare($bi0) == 0) { 
                $gPowExpModP = $g->modPow($exp, $p);
                if($gPowExpModP->compare($bi1) == 0) { return array(); }
            }
        }
        return array($p, $g);
    }

    /**
     * Check the size of required task type and finds group generator (g) and order of group (p)
     * @return array First element of array is order of group (p) and the second is group generator (g)
     */
    private function findGandPPair() : array {
        if($this->sizeType != 16 && $this->sizeType != 32 && $this->sizeType != 48 && $this->sizeType != 64) {
            return array();
        }
        $gAndP = $this->findGandPPairEngine($this->sizeType);
        while(count($gAndP) == 0) {
            $gAndP = $this->findGandPPairEngine($this->sizeType);
        }
        return $gAndP;
    }
    
    /**
     * Initialize the class variables using arguments from GET array
     */
    public function __construct() {
        $this->sizeType = (int)$_GET['size'];
        $this->type = (string)$_GET['type'];
    }
    
    /**
     * Generates and plot required values
     */
    public function generateAndPlotValues() {
        $size = $this->sizeType;
        $type = $this->type;
        if($type === "diffiehellman" || $type === "elgamal") {
            if($size != 16 && $size != 32 && $size != 48 && $size != 64) {
                echo "Error: unsupported bit size!";
            } else {
                $pairGP = $this->findGandPPair();
                $p = $pairGP[0];
                $g = $pairGP[1];
                echo '{';
                echo '"p":"'.$p->toHex().'",';
                echo '"g":"'.$g->toHex().'",';
                if($type === "diffiehellman") {
                    $rand = new Math_BigInteger();
                    $dhA = $rand->random(new Math_BigInteger("1024"), $p);
                    $dhB = $rand->random(new Math_BigInteger("1024"), $p);
                    $dhGPowA = $g->modPow($dhA, $p);
                    $dhGPowB = $g->modPow($dhB, $p);
                    echo '"gPowA":"'.$dhGPowA->toHex().'",';
                    echo '"gPowB":"'.$dhGPowB->toHex().'"';
                } else if($type === "elgamal") {
                    $rand = new Math_BigInteger();

                    $egX = $rand->random(new Math_BigInteger("2"), $p->subtract(new Math_BigInteger("2")));
                    $egGPowX = $g->modPow($egX, $p);

                    $egK = $rand->random(new Math_BigInteger("2"), $p->subtract(new Math_BigInteger("2")));
                    $egGPowK = $g->modPow($egK, $p);

                    $c1 = $egGPowK;

                    $m = $rand->random(new Math_BigInteger("2"), $p);
                    $c2 = $egGPowX->modPow($egK, $p);
                    $c2 = $c2->multiply($m);
                    $c2 = $c2->modPow(new Math_BigInteger("1"), $p);

                    echo '"h":"'.$egGPowX->toHex().'",';
                    echo '"c1":"'.$c1->toHex().'",';
                    echo '"c2":"'.$c2->toHex().'"';
                }
                echo '}';
            }
        }
        else if($type === "rsa") {
            if($size != 64 && $size != 128 && $size != 256) {
                echo "Error: unsupported bit size!";
            } else {
                $i31 = new Math_BigInteger("2147483648");
                $i32 = new Math_BigInteger("4294967295");
                $i63 = new Math_BigInteger("9223372036854775808");
                $i64 = new Math_BigInteger("18446744073709551615");
                $i127 = new Math_BigInteger("170141183460469231731687303715884105728");
                $i128 = new Math_BigInteger("340282366920938463463374607431768211456");
                $rand = new Math_BigInteger();
                $p = $rand->randomPrime($i31, $i32);
                $q = $rand->randomPrime($i31, $i32);
                if($size == 128) {
                    $p = $rand->randomPrime($i63, $i64);
                    $q = $rand->randomPrime($i63, $i64);
                } else if($size == 256) {
                    $p = $rand->randomPrime($i127, $i128);
                    $q = $rand->randomPrime($i127, $i128);
                }
                $n = $p->multiply($q);
                $m = $rand->random(new Math_BigInteger("1024"), $n);

                $pSubtract1 = $p->subtract(new Math_BigInteger("1"));
                $qSubtract1 = $q->subtract(new Math_BigInteger("1"));
                $phiN = $pSubtract1->multiply($qSubtract1);

                $e = $rand->randomPrime(new Math_BigInteger("3"), new Math_BigInteger("131072"));
                $eRemDiv = $phiN->divide($e);
                $eRem = $eRemDiv[1];
                while($eRem->compare(new Math_BigInteger("0")) == 0) {
                    $e = $rand->randomPrime(new Math_BigInteger("3"), new Math_BigInteger("131072"));
                    $eRemDiv = $phiN->divide($e);
                    $eRem = $eRemDiv[1];
                }
                $c = $m->modPow($e, $n);
                echo '{';
                echo '"n":"'.$n->toHex().'",';
                echo '"e":"'.$e->toHex().'",';
                echo '"c":"'.$c->toHex().'"';
                echo '}';
            }
        }
        else {
            echo "Error: unsupported cryptosystem!";
        }
    }
}

//Starts the script:
$init = new RandomTaskGenerator();
$init->generateAndPlotValues();