rem Saved in C:\Users\Nick\Documents\test_log.txt
@echo off
@echo This is a test at %DATE% %TIME%> C:\Users\Nick\Documents\test_log.txt


cd\
cd "C:\Program Files\JAM Software\SpamAssassin for Windows"
spamassassin -e <"C:\Users\Nick\Documents\thomas.txt"> "C:\Users\Nick\Documents\output_%RANDOM%.txt"
cd\
cd "c:\users\nick"
