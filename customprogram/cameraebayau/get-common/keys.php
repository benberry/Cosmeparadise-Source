<?php
    //show all errors - useful whilst developing
    error_reporting(E_ALL);

    // these keys can be obtained by registering at http://developer.ebay.com
    
    $production         = true;   // toggle to true if going against production
    //$compatabilityLevel = 551;    // eBay API version
    
    if ($production) {
        $devID = '3813c739-8486-4856-8798-7ce7d716002f';   // these prod keys are different from sandbox keys
        $appID = 'Camerapa-62d5-4e4f-bf6a-f4cf21fc9642';
        $certID = '53cbb22d-bad4-41fa-b8bb-94e05b81f93c';
        //set the Server to use (Sandbox or Production)
        $serverUrl = 'https://api.ebay.com/ws/api.dll';      // server URL different for prod and sandbox
        //the token representing the eBay user to assign the call with
        $userToken = 'AgAAAA**AQAAAA**aAAAAA**gcW0Uw**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wNk4WnDpaEpgqdj6x9nY+seQ**p1ICAA**AAMAAA**UwGWOCj8JxVojN/oZkXiF6cr/YylhhQNKvj9nnkP4K1odUfkDQsf432K+gHvhmPE/yyXFqkUNiIutxatAMWKbAIIg0Qgg5RTQn1bfP42nZwZofGyv8RE3oJrhHfnAUwASosEiELeD5ifM6a/tMCz7Ah94QBLVt7jD+tAmlNvsJjuqSGoL81T4Czr2EZXqybRZERX8/SgDNyS6TBYIjwEog439KN8tC/RyvUovz8DgLg1I8vxOgDBjDMgKL/owwkGXHBfe5RRF8UtPTHsGUAQFyhyT2DKDKYYPesABsYLduF+Vpk4tVKKF04Z4GttfA90aa5tC4f0z/OmxRKkjqdAI/iY7QV5A/LpeYYdX8rYX0Q+0id0SBevdM26G75/NhLcMs0zvo2Buwp+qyJ/oVdW6LPx37xm5FjH0YaM8pUOeKVpP/C7bk/lSyR6IMHRD/Lk8T+BVc6DaykL5vni5b1S1tXv8TxkKVvG+voGaQDAQEgIFCY/M+9LtqkH13btjqKpaBAVz7uJ1Pb+o/Xa+Qn96H9+5GkGW7f4qh33UHLlHpGg3RqA6G78dAAO87VemcZvtDDEzU+ROXptA9FFrKMwHyYHxFR+usmehi8Vymfl6vZZe73WaL3DtuXRt/4LspC5sR3FTuJLuh6YhmNqH9hiE9bSojHpCvymecgVM5qXChzZjTqNLdPWAuPaxi0WKCN8i61Rs5KvmkwpQ56AYtgdB+/tFezdcSnWxfO/cdjU6UtuK953Qq4S1BbZe5ksmLf6';          
    } else {  
        // sandbox (test) environment
        $devID = '3813c739-8486-4856-8798-7ce7d716002f';         // insert your devID for sandbox
        $appID = 'Camerapa-428e-4451-946a-2665c0390d91';   // different from prod keys
        $certID = 'e666191f-fb43-4602-8286-da468cae2d60';  // need three 'keys' and one token
        //set the Server to use (Sandbox or Production)
        $serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        // the token representing the eBay user to assign the call with
        // this token is a long string - don't insert new lines - different from prod token
        $userToken = 'AgAAAA**AQAAAA**aAAAAA**htzlUg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhC5iCpwSdj6x9nY+seQ**CYECAA**AAMAAA**9c1X4K3+RtoKu1Z56gqpIDUlXzHFRGQEPuvEJcs+G1W/HsJe5bpMb3Y8ZzAcMlYkDj2419cbC76mbwkvKAfzAQEonp3LoKvPDyKnbvVJxZeLsYgCxPivaiT7b0L0/nVZBBW1pYXb/fDJrDNwFyRLVndPGFzTzIxyAm9Hv/459Y9Jit/jLM4CJGTcvQlDzJCrBquxwjLlP808BRg5n+BngNm3Cpvm7C+AEtIJbtPgA+TJs0094KM8CTfiLeZYpZc6+sdJ5anMwPd4tDeLbggWJ7APKEVzWjsLOhpWsLU3ZdcCFEXXheBYruwD5nscVhaR1EVSWG6yd8cmOstUu1feNFm+Sds/a7KVELaweAhT6VuP3cwjfCWKOXXQxlqRizRlHWKa3pB4yIeKA3aqus7K1NkMQDlrZit72Jqll8Yza+fgKRGxm8YRnVNPYcoYMpcB/Q2dmSMLTeKuBlpu4wSAL7pRecRimxkHpJ7pf5rxwiUjrAKsjIm/Ss4mVINiBCbWePmhYFBWWi4Yglzr4QslPOn59sOkyCKvRkiIgAJjzZN4870L730e+HWbe/7SEbgnjMkAfCBTX9/BNKx2lxs24Z/uUOBLqKAohns0us9xXS+DDhqaKG5cFabidDBO17FiXxRKz0EW3owYUaZtQQPKgO7iq1lGN/ytmYme1uGzsnDa/KElEbJrRY8e7lMFmKcuHUVwYtU4HvWYWaxzJQ/9ICKeczfDjQLx0LU129wTNXXQuXi5kuaHhukXj8VDDqLn';                 
    }
    
    
?>