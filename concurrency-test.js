const axios = require('axios');

const BASE_URL = 'http://127.0.0.1:8000/api/v1';

// Replace these with your actual Postman tokens/IDs
const USER_TOKEN = 'YOUR_BEARER_TOKEN_HERE'; 
const SLOT_ID = 'YOUR_AVAILABLE_SLOT_ID_HERE'; 
const TEST_DATE = '2026-03-01'; // Make sure this matches the slot you test
const REQUEST_COUNT = 50; // Simulate 50 concurrent requests

async function runConcurrencyTest() {
    console.log(`🚀 Starting Concurrency Test with ${REQUEST_COUNT} simultaneous requests...`);
    console.log(`Target Slot ID: ${SLOT_ID}`);
    
    // We will store all the promises here
    const requests = [];

    // Create 50 simultaneous lock requests
    for (let i = 0; i < REQUEST_COUNT; i++) {
        const req = axios.post(`${BASE_URL}/slots/${SLOT_ID}/lock`, {
            date: TEST_DATE
        }, {
            headers: {
                'Authorization': `Bearer ${USER_TOKEN}`,
                'Accept': 'application/json'
            },
            // Prevent axios from throwing error on 400+ status codes
            validateStatus: () => true 
        }).then(response => ({
            id: i + 1,
            status: response.status,
            data: response.data
        }));

        requests.push(req);
    }

    // Await all of them firing simultaneously
    const startTime = Date.now();
    const results = await Promise.all(requests);
    const timeTaken = Date.now() - startTime;

    // Analyze the results
    let successCount = 0;
    let conflictCount = 0;
    let otherErrorsCount = 0;

    results.forEach(res => {
        if (res.status === 200) {
            successCount++;
        } else if (res.status === 409) {
            conflictCount++; // 409 means Slot Unavailable
        } else {
            otherErrorsCount++;
            console.log(`[Request ${res.id}] Unexpected Status: ${res.status}`, res.data);
        }
    });

    console.log('\n=======================================');
    console.log(`✅ Test Completed in ${timeTaken} ms`);
    console.log(`🟢 Successful Locks: ${successCount} (EXPECTED: 1)`);
    console.log(`🔴 Prevented Double Locks (409): ${conflictCount} (EXPECTED: ${REQUEST_COUNT - 1})`);
    console.log(`🟡 Other Errors: ${otherErrorsCount}`);
    console.log('=======================================');

    if (successCount === 1 && conflictCount === (REQUEST_COUNT - 1)) {
        console.log('\n🏆 CONCURRENCY TEST PASSED! The DB locking mechanism correctly blocked all racing conditions.');
    } else {
        console.log('\n❌ CONCURRENCY TEST FAILED! Unexpected results.');
    }
}

// Ensure the user actually input their tokens
if (USER_TOKEN === 'YOUR_BEARER_TOKEN_HERE' || SLOT_ID === 'YOUR_AVAILABLE_SLOT_ID_HERE') {
    console.log('⚠️ PLEASE UPDATE THE \'USER_TOKEN\' AND \'SLOT_ID\' VARIABLES IN THE SCRIPT BEFORE RUNNING!');
} else {
    runConcurrencyTest();
}
