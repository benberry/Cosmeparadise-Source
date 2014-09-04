<?php
    //show all errors - useful whilst developing
    error_reporting(E_ALL);

    // these keys can be obtained by registering at http://developer.ebay.com
    
    $production         = true;   // toggle to true if going against production
    //$compatabilityLevel = 551;    // eBay API version
    
    if ($production) {
        $devID = '05667b1c-3c83-4fc8-a994-df369e9e5304';   // these prod keys are different from sandbox keys
        $appID = 'Uniondut-7c56-4493-9553-a0f23992268d';
        $certID = '824bfee1-dbc5-4a7e-9284-9a12d6312638';
        //set the Server to use (Sandbox or Production)
        $serverUrl = 'https://api.ebay.com/ws/api.dll';      // server URL different for prod and sandbox
        //the token representing the eBay user to assign the call with
        $userToken = 'AgAAAA**AQAAAA**aAAAAA**cdnlUg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AEkICiD5SBoQydj6x9nY+seQ**phkCAA**AAMAAA**NSp1wxn8jzX1OGMjhgiCSe5p7dbDlXhmJ0PrDkeOhM1AKYEaBB5e1xChCW9qxfujZrFq7Vp/0kW0/G3uX6I6zA5Dh2seOR/UXFnCVhT2E9m9dqEDBPo0hSUdUH08lGb9OuE6nB70ll5e3jr5ga/+OSwrhZPpSAWvd0MpEdBJzHO3E0DAl0Eton7l7fyFuiGPBc9wbOSaCFubFF2jkvUdZOrzAlddMtDkZ2gWUn7zdEC+cXYmulQbJWZQHtK98dHvfODb/bPT3c6icatjkFRP4XuffSFqQ3+KHi9tIl+pjPEHuMZ4/oXdbFKAaLCTrdtbm8zbRALvkRDQswIeNqE9tISlCq3CO8Jgw/YyGNTty72+oJgz0b4JwwcJmQFVP0aE7xw2CF5ByrNOrTNG7hCN1QecgaUCPsCp3Zbx+PIBSXNeURMsea5n5MxoNX8KUUd5stH/wUUpQCUyLb/UgxXjJnt40bvV5jTN9Y2R7bf87mWY7THT9trMgXk4gt38vDTsUxgWZLktPSbHl0MJA/aSjmCBU8A7dJpS2rp5G61kMTFk3fXxtVY/FJECY1wUt0nFQEQi16J6rrwIPpihAUNZIeas7ePLVCyu8gAwARQEfBREAv/MTy4HUUFdC92vXWQJrRNKeauBbpYcOWtxidaAkuA2bitCdQ1MJ5BYtIyrzsCEHseGfZZkpAO3mMwYzuozP7iMbNkY9ijzO3C58s0bn132MGc/UsQYGTcTEODgN3J3v2iHSpaXCqaqogRyoeBN';          
    } else {  
        // sandbox (test) environment
        $devID = '05667b1c-3c83-4fc8-a994-df369e9e5304';         // insert your devID for sandbox
        $appID = 'Uniondut-41bb-4e5e-a177-77e21ee59e2e';   // different from prod keys
        $certID = 'd7b3bef5-7ca3-46e2-900b-643471d6f628';  // need three 'keys' and one token
        //set the Server to use (Sandbox or Production)
        $serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        // the token representing the eBay user to assign the call with
        // this token is a long string - don't insert new lines - different from prod token
        $userToken = 'AgAAAA**AQAAAA**aAAAAA**htzlUg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhC5iCpwSdj6x9nY+seQ**CYECAA**AAMAAA**9c1X4K3+RtoKu1Z56gqpIDUlXzHFRGQEPuvEJcs+G1W/HsJe5bpMb3Y8ZzAcMlYkDj2419cbC76mbwkvKAfzAQEonp3LoKvPDyKnbvVJxZeLsYgCxPivaiT7b0L0/nVZBBW1pYXb/fDJrDNwFyRLVndPGFzTzIxyAm9Hv/459Y9Jit/jLM4CJGTcvQlDzJCrBquxwjLlP808BRg5n+BngNm3Cpvm7C+AEtIJbtPgA+TJs0094KM8CTfiLeZYpZc6+sdJ5anMwPd4tDeLbggWJ7APKEVzWjsLOhpWsLU3ZdcCFEXXheBYruwD5nscVhaR1EVSWG6yd8cmOstUu1feNFm+Sds/a7KVELaweAhT6VuP3cwjfCWKOXXQxlqRizRlHWKa3pB4yIeKA3aqus7K1NkMQDlrZit72Jqll8Yza+fgKRGxm8YRnVNPYcoYMpcB/Q2dmSMLTeKuBlpu4wSAL7pRecRimxkHpJ7pf5rxwiUjrAKsjIm/Ss4mVINiBCbWePmhYFBWWi4Yglzr4QslPOn59sOkyCKvRkiIgAJjzZN4870L730e+HWbe/7SEbgnjMkAfCBTX9/BNKx2lxs24Z/uUOBLqKAohns0us9xXS+DDhqaKG5cFabidDBO17FiXxRKz0EW3owYUaZtQQPKgO7iq1lGN/ytmYme1uGzsnDa/KElEbJrRY8e7lMFmKcuHUVwYtU4HvWYWaxzJQ/9ICKeczfDjQLx0LU129wTNXXQuXi5kuaHhukXj8VDDqLn';                 
    }
    
    
?>