module.exports = async function(t) {
  try {
    const [method,  payload, headers] = ['PUT', {}, t.context.config.getHeader()];
    const app = t.context.app;

    const memberList = await app.inject({method: 'GET', url: '/admin/members', headers});
    t.equal(memberList.statusCode, 200, 'the member list is received');
    const {members, stats} = memberList.json();
    t.equal(members.length, 1, 'the member length is 1');
    t.equal(stats.total, 1, 'The total number is 1');
    t.equal(stats.approved, 0, 'The approved members are 0');
    t.equal(stats.pending, 1, 'the pending is 1');

    const member = members[0];
    const member_id = member.id;
    const url = `/admin/members/${member_id}`;
    const missingStatus = await app.inject({method, url, payload, headers});
    t.equal(missingStatus.statusCode, 400, 'The status is missing');

    payload.status = 'something';
    const wrongStatus = await app.inject({method, url, payload, headers});
    t.equal(wrongStatus.statusCode, 400, 'The status is not in the enum');

    payload.status = 'approved';
    const approvedStatus = await app.inject({method, url, payload, headers});
    t.equal(approvedStatus.statusCode, 200, 'The status updated');

    payload.status = 'suspended';
    const suspendedStatus = await app.inject({method, url, payload, headers});
    t.equal(suspendedStatus.statusCode, 200, 'The status set to suspended');

  } catch(error) {
    throw error;
  }
}