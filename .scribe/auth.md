# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer Bearer {your-token}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Authenticate using a Bearer token obtained from POST /api/login.
