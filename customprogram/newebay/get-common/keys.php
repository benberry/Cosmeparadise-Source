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
        $userToken = 'AgAAAA**AQAAAA**aAAAAA**PXjYUw**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AFk4ekAJaCoQydj6x9nY+seQ**phkCAA**AAMAAA**DRlxfNRtPui1Y58+5s2xyEC4jhL1SHIykiSpGKTZDH4QUSpXTDtz6kw4xnXJ+8PTd13LAvyxFE3RohqAEe+d8R/E756IHQpacsvNmUkS2C04dZhJWUf3aKASvBHvKbwxrtIjPsLqpMqWEtfoslHz2NoBHylbCUIixEwdsnuMpDM5dgrxb6y5cfDIvmBSmKQDEWTAp/WHoWOtm+/CxVhW5tNGrj+pgG+IwAJkFExK1T0peQ2SdTSjfNyB3BrnHi+ZLflmZYCtfznifWg2utKtEAlXrDBTgILZt5aY44/3Nnzd7eREmyGaJszNHnizmLV2Z1OXIyTMSbLLDV2LNuj3V07P/3UkQWIR2ZeNbWSAx0D4uqJ3hHOBUOHxldUScc2gNJ7HZnpVYcDXVUgFtjLQ51ZWUy6s/CGH+IxWIWd+IbMb4C+yUsrSzm/374VrhtYKsHihfpfM6ehOr77JzMHe7xEDZ9AjafjTCWV25zyyDGOEd1SweEashoVxdL7ygQc3NDh4qaX6FCpQUJP5WEPzhpu4btdSlvL89Hi6hKNrPhZW2EJwGSoQzP26LQStQOTTfT/uhJhtIIlNSrKsOUdXLkVWe+E3qm9WDI/t4OT+Hdl18XeOFX7+DJeLAmrQ4DMY42xN25AWvmIaXqtoACkgX8XZXUFdvcz5bY5inpeKu5xaSq6ml/v98K9FfElXhu1/p6TnWOUEqbMUEZajxnFyvlWx9uUyKYSDAmWAAf4i+EWgsI8W5nKpjQWRpi4EPlr0';          
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