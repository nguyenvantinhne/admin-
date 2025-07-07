const functions = require('firebase-functions');
const admin = require('firebase-admin');
admin.initializeApp();

const db = admin.firestore();
const auth = admin.auth();

// API lấy danh sách user chờ duyệt
exports.getPendingUsers = functions.https.onCall(async (data, context) => {
  if (!context.auth) throw new functions.https.HttpsError('unauthenticated', 'Authentication required');
  
  const snapshot = await db.collection('users')
    .where('approved', '==', false)
    .get();

  return snapshot.docs.map(doc => ({
    id: doc.id,
    ...doc.data()
  }));
});

// API duyệt user
exports.approveUser = functions.https.onCall(async (data, context) => {
  if (!context.auth) throw new functions.https.HttpsError('unauthenticated', 'Authentication required');

  const { userId } = data;
  await db.collection('users').doc(userId).update({
    approved: true,
    approvedAt: admin.firestore.FieldValue.serverTimestamp()
  });
  
  return { success: true };
});

// API từ chối user
exports.rejectUser = functions.https.onCall(async (data, context) => {
  if (!context.auth) throw new functions.https.HttpsError('unauthenticated', 'Authentication required');

  const { userId } = data;
  await auth.deleteUser(userId);
  await db.collection('users').doc(userId).delete();
  
  return { success: true };
});
