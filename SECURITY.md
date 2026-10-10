# Security Policy

## Reporting a vulnerability

Please do not report security vulnerabilities in public issues. Use GitHub's private vulnerability reporting for this repository: <https://github.com/Digit7s/inertia-form/security/advisories/new>. If private reporting is unavailable, email the maintainer at `myomin.oo@digit7s.com` and include only the details needed to reproduce the issue.

The maintainer will acknowledge reports as soon as practical and coordinate a fix and disclosure with the reporter. Please avoid publishing exploit details before a fix is available.

## Supported versions

Security fixes are intended for the latest supported stable major version. The support policy for v1.0.0 will be reviewed when v2.0.0 is released.

## Application responsibilities

This package describes fields and submits browser data. It does not authorize access, validate submitted values, authorize relationship IDs, or sanitize stored rich text. Applications must enforce those controls on the server.
