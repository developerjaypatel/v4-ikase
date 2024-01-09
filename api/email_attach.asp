<%@LANGUAGE="VBSCRIPT" CODEPAGE="65001"%>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<%
' This CreateObject statement uses the new single-DLL ActiveX for v9.5.0
set imap = Server.CreateObject("Chilkat_9_5_0.Imap")

'  Anything unlocks the component and begins a fully-functional 30-day trial.
success = imap.UnlockComponent("Anything for 30-day trial")
If (success <> 1) Then
    Response.Write "<pre>" & Server.HTMLEncode( imap.LastErrorText) & "</pre>"

End If

'  Connect to an IMAP server.
success = imap.Connect("mail.glauberberenson.com")
If (success <> 1) Then
    Response.Write "<pre>" & Server.HTMLEncode( imap.LastErrorText) & "</pre>"

End If

'  Login
success = imap.Login("aamirkhanian@glauberberenson.com","amirkhanian*")
If (success <> 1) Then
    Response.Write "<pre>" & Server.HTMLEncode( imap.LastErrorText) & "</pre>"

End If

'  Select an IMAP mailbox
success = imap.SelectMailbox("Inbox")
If (success <> 1) Then
    Response.Write "<pre>" & Server.HTMLEncode( imap.LastErrorText) & "</pre>"

End If

'  We can choose to fetch UIDs or sequence numbers.
fetchUids = 1

'  Get the message IDs of all the emails in the mailbox
' messageSet is a Chilkat_9_5_0.MessageSet
Set messageSet = imap.Search("ALL",fetchUids)
If (messageSet Is Nothing ) Then
    Response.Write "<pre>" & Server.HTMLEncode( imap.LastErrorText) & "</pre>"

End If

'  Fetch the email headers into a bundle object:

' bundle is a Chilkat_9_5_0.EmailBundle
Set bundle = imap.FetchHeaders(messageSet)
If (bundle Is Nothing ) Then

    Response.Write "<pre>" & Server.HTMLEncode( imap.LastErrorText) & "</pre>"

End If

'  Scan for emails with attachments, and save the attachments
'  to a sub-directory.

'For i = 0 To bundle.MessageCount - 1
For i = 0 To 10

    ' email is a Chilkat_9_5_0.Email
    Set email = bundle.GetEmail(i)

    '  Does this email have attachments?
    '  Use GetMailNumAttach because the attachments
    '  are not actually in the email object because
    '  we only downloaded headers.
    '  (Had we downloaded the full emails by
    '  calling mailman.FetchBundle, we could look
    '  at the email object's NumAttachments property.)
    numAttach = imap.GetMailNumAttach(email)

    If (numAttach > 0) Then
        '  Download the entire email and save the
        '  attachments. (Remember, we
        '  need to download the entire email because
        '  only the headers were previously downloaded.
        '  If the entire emails were downloaded by
        '  calling FetchBundle instead of FetchHeaders,
        '  this would not be necessary.

        '  The ckx-imap-uid header field is added when
        '  headers are downloaded.  This makes it possible
        '  to get the UID from the email object.
        uidStr = email.GetHeaderField("ckx-imap-uid")
        uid = CLng(uidStr)

        ' fullEmail is a Chilkat_9_5_0.Email
        Set fullEmail = imap.FetchSingle(uid,1)
        If (Not (fullEmail Is Nothing )) Then
            fullEmail.SaveAllAttachments "attachmentsDir"

        End If

        For j = 0 To numAttach - 1
            filename = imap.GetMailAttachFilename(email,j)
            Response.Write "<pre>" & Server.HTMLEncode( filename) & "</pre>"
        Next

    End If

Next

'  Disconnect from the IMAP server.
imap.Disconnect 


%>
</body>
</html>